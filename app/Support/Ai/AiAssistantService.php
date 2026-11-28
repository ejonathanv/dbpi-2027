<?php

namespace App\Support\Ai;

use App\Enums\AiChatConversationStatus;
use App\Enums\AiChatMessageRole;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AiAssistantService
{
    public function __construct(
        protected OpenAiChatClient $client,
        protected AiUserContextBuilder $contextBuilder,
        protected AiToolRegistry $toolRegistry,
        protected AiToolExecutor $toolExecutor,
    ) {}

    public function processTurn(int $conversationId): void
    {
        $conversation = AiChatConversation::query()->with('user')->findOrFail($conversationId);
        $user = $conversation->user;

        if (! $user instanceof User) {
            return;
        }

        $rateKey = 'ai-assistant:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($rateKey, config('ai-assistant.rate_limit.max_attempts', 30))) {
            $this->failConversation($conversation, 'Has alcanzado el límite de solicitudes. Intenta más tarde.');

            return;
        }

        RateLimiter::hit($rateKey, config('ai-assistant.rate_limit.decay_seconds', 3600));

        $latestMessage = $conversation->messages()->latest('id')->first();

        if ($latestMessage?->role !== AiChatMessageRole::User) {
            if ($conversation->status === AiChatConversationStatus::Processing) {
                $conversation->update(['status' => AiChatConversationStatus::Idle]);
            }

            Log::channel('ai')->info('ai_turn_skipped', [
                'conversation_id' => $conversation->getKey(),
                'reason' => 'last_message_not_user',
            ]);

            return;
        }

        try {
            $messages = $this->buildApiMessages($conversation, $user);
            $tools = $this->toolRegistry->toolsForUser($user);
            $assistantText = '';
            $iterations = 0;
            $maxIterations = config('ai-assistant.max_tool_iterations', 6);

            while ($iterations < $maxIterations) {
                $iterations++;
                $response = $this->client->chat($messages, $tools);
                $choice = $response['choices'][0]['message'] ?? [];
                $toolCalls = $choice['tool_calls'] ?? null;

                if (empty($toolCalls)) {
                    $assistantText = (string) ($choice['content'] ?? '');

                    break;
                }

                $messages[] = [
                    'role' => 'assistant',
                    'content' => $choice['content'] ?? null,
                    'tool_calls' => $toolCalls,
                ];

                foreach ($toolCalls as $toolCall) {
                    $name = (string) ($toolCall['function']['name'] ?? '');
                    $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];
                    $result = $this->toolExecutor->execute($user, $name, $arguments);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => $result['content'],
                    ];

                    AiChatMessage::query()->create([
                        'ai_chat_conversation_id' => $conversation->getKey(),
                        'role' => AiChatMessageRole::Tool,
                        'content' => $result['content'],
                        'meta' => ['tool' => $name],
                    ]);
                }
            }

            if ($assistantText === '') {
                $assistantText = 'Listo.';
            }

            if (blank($conversation->title) && $conversation->messages()->where('role', AiChatMessageRole::User)->exists()) {
                $firstUser = $conversation->messages()->where('role', AiChatMessageRole::User)->first();
                $conversation->title = Str::limit((string) $firstUser?->content, 60);
            }

            AiChatMessage::query()->create([
                'ai_chat_conversation_id' => $conversation->getKey(),
                'role' => AiChatMessageRole::Assistant,
                'content' => $assistantText,
            ]);

            $conversation->update([
                'status' => AiChatConversationStatus::Idle,
                'last_error' => null,
            ]);

            $toolNames = $conversation->messages()
                ->where('role', AiChatMessageRole::Tool)
                ->where('created_at', '>=', $latestMessage->created_at)
                ->pluck('meta')
                ->map(fn (?array $meta): ?string => is_array($meta) ? ($meta['tool'] ?? null) : null)
                ->filter()
                ->values()
                ->all();

            Log::channel('ai')->info('ai_turn_completed', [
                'conversation_id' => $conversation->getKey(),
                'user_id' => $user->getKey(),
                'iterations' => $iterations,
                'tools' => $toolNames,
            ]);
        } catch (\Throwable $exception) {
            Log::channel('ai')->warning('ai_turn_failed', [
                'conversation_id' => $conversation->getKey(),
                'message' => $exception->getMessage(),
            ]);

            $this->failConversation($conversation, 'No pude procesar la solicitud. Verifica la configuración de IA o inténtalo de nuevo.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildApiMessages(AiChatConversation $conversation, User $user): array
    {
        $messages = [[
            'role' => 'system',
            'content' => $this->contextBuilder->systemPrompt($user),
        ]];

        $historyLimit = config('ai-assistant.max_history_messages', 24);

        $historyIds = $conversation->messages()
            ->whereIn('role', [
                AiChatMessageRole::User->value,
                AiChatMessageRole::Assistant->value,
            ])
            ->orderByDesc('id')
            ->limit($historyLimit)
            ->pluck('id');

        $history = AiChatMessage::query()
            ->whereIn('id', $historyIds)
            ->orderBy('id')
            ->get();

        foreach ($history as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => (string) $message->content,
            ];
        }

        return $messages;
    }

    protected function failConversation(AiChatConversation $conversation, string $error): void
    {
        $conversation->update([
            'status' => AiChatConversationStatus::Error,
            'last_error' => $error,
        ]);

        AiChatMessage::query()->create([
            'ai_chat_conversation_id' => $conversation->getKey(),
            'role' => AiChatMessageRole::Assistant,
            'content' => $error,
        ]);
    }
}
