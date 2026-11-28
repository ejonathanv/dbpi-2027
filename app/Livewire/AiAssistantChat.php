<?php

namespace App\Livewire;

use App\Enums\AiChatConversationStatus;
use App\Enums\AiChatMessageRole;
use App\Jobs\ProcessAiChatTurn;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\User;
use App\Support\Ai\AiAgentCatalog;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class AiAssistantChat extends Component
{
    public bool $isOpen = false;

    public ?int $conversationId = null;

    public string $prompt = '';

    public bool $awaitingResponse = false;

    public ?string $expandedCategory = null;

    public string $panel = 'conversation';

    public function mount(): void
    {
        $this->ensureConversation();
        $this->syncAwaitingFlag();
    }

    public function syncConversation(): void
    {
        if ($this->panel !== 'conversation' || $this->conversationId === null) {
            return;
        }

        if (! $this->awaitingResponse && ! $this->conversationIsProcessing()) {
            return;
        }

        $this->syncAwaitingFlag();
    }

    /**
     * @return Collection<int, AiChatMessage>
     */
    public function getMessagesProperty(): Collection
    {
        if ($this->conversationId === null) {
            return collect();
        }

        return AiChatMessage::query()
            ->where('ai_chat_conversation_id', $this->conversationId)
            ->whereIn('role', [AiChatMessageRole::User, AiChatMessageRole::Assistant])
            ->orderBy('id')
            ->get();
    }

    public function isProcessing(): bool
    {
        return $this->awaitingResponse || $this->conversationIsProcessing();
    }

    public function sendMessage(): void
    {
        if (! config('ai-assistant.enabled', true)) {
            return;
        }

        $text = trim($this->prompt);

        if ($text === '' || $this->isProcessing()) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $conversation = $this->ensureConversation();

        AiChatMessage::query()->create([
            'ai_chat_conversation_id' => $conversation->getKey(),
            'role' => AiChatMessageRole::User,
            'content' => $text,
        ]);

        $conversation->update(['status' => AiChatConversationStatus::Processing]);

        $this->prompt = '';
        $this->awaitingResponse = true;

        if (config('ai-assistant.process_sync', false)) {
            ProcessAiChatTurn::dispatchSync($conversation->getKey());
            $this->syncAwaitingFlag();
        } else {
            ProcessAiChatTurn::dispatch($conversation->getKey());
        }

        $this->dispatch('ai-chat-scroll');
    }

    public function startNewConversation(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $conversation = AiChatConversation::query()->create([
            'user_id' => $user->getKey(),
            'status' => AiChatConversationStatus::Idle,
        ]);

        $this->conversationId = $conversation->getKey();
        $this->prompt = '';
        $this->awaitingResponse = false;
        $this->expandedCategory = null;
        $this->panel = 'conversation';
    }

    public function showConversationList(): void
    {
        $this->panel = 'list';
    }

    public function openConversation(int $conversationId): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $conversation = AiChatConversation::query()
            ->whereKey($conversationId)
            ->where('user_id', $user->getKey())
            ->first();

        if (! $conversation) {
            return;
        }

        $this->conversationId = $conversation->getKey();
        $this->panel = 'conversation';
        $this->prompt = '';
        $this->expandedCategory = null;
        $this->syncAwaitingFlag();
    }

    public function deleteConversation(int $conversationId): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $conversation = AiChatConversation::query()
            ->whereKey($conversationId)
            ->where('user_id', $user->getKey())
            ->first();

        if (! $conversation) {
            return;
        }

        $wasActive = $this->conversationId === $conversation->getKey();

        $conversation->delete();

        if ($wasActive) {
            $this->conversationId = null;
            $this->awaitingResponse = false;
            $this->panel = 'list';
        }

        Notification::make()
            ->title('Chat eliminado')
            ->success()
            ->send();
    }

    public function deleteCurrentConversation(): void
    {
        if ($this->conversationId === null) {
            return;
        }

        $this->deleteConversation($this->conversationId);
    }

    public function applySuggestion(string $text): void
    {
        $this->prompt = $text;
    }

    public function toggleCategory(string $id): void
    {
        $this->expandedCategory = $this->expandedCategory === $id ? null : $id;
    }

    public function runAgentPrompt(string $text): void
    {
        $this->prompt = trim($text);

        if ($this->prompt === '') {
            return;
        }

        $this->sendMessage();
    }

    public function getConversationTitleProperty(): string
    {
        if ($this->panel === 'list') {
            return 'Chats';
        }

        if ($this->conversationId === null) {
            return 'Nuevo chat';
        }

        return $this->displayTitleFor(
            AiChatConversation::query()->find($this->conversationId),
        );
    }

    /**
     * @return Collection<int, AiChatConversation>
     */
    public function getConversationsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return collect();
        }

        return AiChatConversation::query()
            ->where('user_id', $user->getKey())
            ->with(['messages' => function ($query): void {
                $query
                    ->whereIn('role', [AiChatMessageRole::User, AiChatMessageRole::Assistant])
                    ->latest('id')
                    ->limit(1);
            }])
            ->latest('updated_at')
            ->limit(60)
            ->get();
    }

    protected function displayTitleFor(?AiChatConversation $conversation): string
    {
        if (! $conversation) {
            return 'Nuevo chat';
        }

        if (filled($conversation->title)) {
            return (string) $conversation->title;
        }

        $preview = $conversation->messages->first()?->content;

        return filled($preview)
            ? Str::limit((string) $preview, 48)
            : 'Nuevo chat';
    }

    protected function ensureConversation(): AiChatConversation
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            throw new \RuntimeException('Usuario no autenticado.');
        }

        if ($this->conversationId !== null) {
            $existing = AiChatConversation::query()
                ->whereKey($this->conversationId)
                ->where('user_id', $user->getKey())
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $conversation = AiChatConversation::query()
            ->where('user_id', $user->getKey())
            ->latest('updated_at')
            ->first();

        if (! $conversation) {
            $conversation = AiChatConversation::query()->create([
                'user_id' => $user->getKey(),
                'status' => AiChatConversationStatus::Idle,
            ]);
        }

        $this->conversationId = $conversation->getKey();

        return $conversation;
    }

    protected function conversationIsProcessing(): bool
    {
        if ($this->conversationId === null) {
            return false;
        }

        return AiChatConversation::query()
            ->whereKey($this->conversationId)
            ->where('status', AiChatConversationStatus::Processing)
            ->exists();
    }

    protected function syncAwaitingFlag(): void
    {
        if ($this->conversationId === null) {
            $this->awaitingResponse = false;

            return;
        }

        $conversation = AiChatConversation::query()->find($this->conversationId);

        $wasAwaiting = $this->awaitingResponse;
        $this->awaitingResponse = $conversation?->status === AiChatConversationStatus::Processing;

        if ($wasAwaiting && ! $this->awaitingResponse) {
            $this->dispatch('ai-chat-scroll');
        }
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.ai-assistant-chat', [
            'messages' => $this->messages,
            'processing' => $this->isProcessing(),
            'conversationTitle' => $this->conversationTitle,
            'conversations' => $this->conversations,
            'panel' => $this->panel,
            'agentCategories' => $user instanceof User
                ? AiAgentCatalog::categoriesFor($user)
                : [],
        ]);
    }
}
