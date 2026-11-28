@php
    use App\Enums\AiChatMessageRole;
    use Filament\Support\Icons\Heroicon;
@endphp

<div
    class="fi-ai-assistant"
    @if ($panel === 'conversation')
        wire:poll.1.5s="syncConversation"
    @endif
>
    <x-filament::modal
        id="ai-assistant-chat"
        slide-over
        teleport="body"
        width="xl"
        class="fi-ai-assistant-modal"
        :sticky-header="true"
        :sticky-footer="true"
    >
        <x-slot name="trigger">
            <x-filament::icon-button
                color="gray"
                :icon="Heroicon::OutlinedSparkles"
                icon-size="lg"
                label="Asistente IA"
                class="fi-topbar-ai-assistant-btn"
            />
        </x-slot>

        <x-slot name="heading">
            <div @class([
                'fi-ai-assistant-heading',
                'fi-ai-assistant-heading--conversation' => $panel === 'conversation',
            ])>
                @if ($panel === 'conversation')
                    <x-filament::icon-button
                        color="gray"
                        icon="heroicon-m-chevron-left"
                        label="Volver al listado"
                        class="fi-ai-assistant-heading__back"
                        wire:click="showConversationList"
                    />
                @endif
                <div class="fi-ai-assistant-heading__text">
                    <span
                        class="fi-ai-assistant-heading__title"
                        title="{{ $conversationTitle }}"
                    >{{ $conversationTitle }}</span>
                    <span class="fi-ai-assistant-heading__meta">
                        @if ($panel === 'list')
                            {{ $conversations->count() }} {{ $conversations->count() === 1 ? 'conversación' : 'conversaciones' }}
                        @else
                            Privado · solo tú
                        @endif
                    </span>
                </div>
            </div>
        </x-slot>

        <x-slot name="headerActions">
            @if ($panel === 'list')
                <x-filament::button
                    size="sm"
                    color="primary"
                    icon="heroicon-m-plus"
                    wire:click="startNewConversation"
                >
                    Nuevo chat
                </x-filament::button>
            @else
                <x-filament::dropdown placement="bottom-end">
                    <x-slot name="trigger">
                        <x-filament::icon-button
                            color="gray"
                            icon="heroicon-m-ellipsis-horizontal"
                            label="Opciones del chat"
                        />
                    </x-slot>

                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item
                            icon="heroicon-o-plus"
                            tag="button"
                            wire:click="startNewConversation"
                        >
                            Nuevo chat
                        </x-filament::dropdown.list.item>
                        @if ($conversationId)
                            <x-filament::dropdown.list.item
                                icon="heroicon-o-trash"
                                tag="button"
                                color="danger"
                                wire:click="deleteCurrentConversation"
                                wire:confirm="¿Eliminar este chat y todos sus mensajes?"
                            >
                                Eliminar chat
                            </x-filament::dropdown.list.item>
                        @endif
                    </x-filament::dropdown.list>
                </x-filament::dropdown>
            @endif
        </x-slot>

        <div class="fi-ai-assistant-main">
            @if ($panel === 'list')
                <div class="fi-ai-assistant-chat-list">
                    @forelse ($conversations as $conversation)
                        @php
                            $lastMessage = $conversation->messages->first();
                            $listTitle = filled($conversation->title)
                                ? $conversation->title
                                : ($lastMessage?->content ? \Illuminate\Support\Str::limit($lastMessage->content, 48) : 'Nuevo chat');
                            $preview = $lastMessage?->content
                                ? \Illuminate\Support\Str::limit($lastMessage->content, 72)
                                : 'Sin mensajes aún';
                        @endphp
                        <div
                            wire:key="ai-conv-{{ $conversation->id }}"
                            @class([
                                'fi-ai-assistant-chat-row',
                                'fi-ai-assistant-chat-row--active' => $conversationId === $conversation->id,
                            ])
                        >
                            <button
                                type="button"
                                class="fi-ai-assistant-chat-row__open"
                                wire:click="openConversation({{ $conversation->id }})"
                            >
                                <span class="fi-ai-assistant-chat-row__title">{{ $listTitle }}</span>
                                <span class="fi-ai-assistant-chat-row__preview">{{ $preview }}</span>
                                <span class="fi-ai-assistant-chat-row__meta">
                                    {{ $conversation->updated_at?->diffForHumans() }}
                                </span>
                            </button>
                            <x-filament::icon-button
                                color="danger"
                                icon="heroicon-m-trash"
                                label="Eliminar chat"
                                class="fi-ai-assistant-chat-row__delete"
                                wire:click="deleteConversation({{ $conversation->id }})"
                                wire:confirm="¿Eliminar este chat y todos sus mensajes?"
                            />
                        </div>
                    @empty
                        <div class="fi-ai-assistant-chat-list__empty">
                            <p class="fi-ai-assistant-chat-list__empty-title">Aún no hay chats</p>
                            <p class="fi-ai-assistant-chat-list__empty-text">
                                Inicia uno nuevo para conversar con el asistente.
                            </p>
                            <x-filament::button
                                class="mt-4"
                                color="primary"
                                icon="heroicon-m-plus"
                                wire:click="startNewConversation"
                            >
                                Nuevo chat
                            </x-filament::button>
                        </div>
                    @endforelse
                </div>
            @else
                @if ($messages->isEmpty())
                    <div class="fi-ai-assistant-empty">
                        <div class="fi-ai-assistant-empty__badge" aria-hidden="true">
                            <x-filament::icon
                                :icon="Heroicon::OutlinedSparkles"
                                class="fi-ai-assistant-empty__badge-icon"
                            />
                        </div>
                        <p class="fi-ai-assistant-empty__title">¿Qué sigue?</p>
                        <p class="fi-ai-assistant-empty__subtitle">
                            Elige un agente o escribe abajo lo que necesitas.
                        </p>

                        <ul class="fi-ai-assistant-agents" role="list">
                            @foreach ($agentCategories as $category)
                                <li
                                    class="fi-ai-assistant-agent"
                                    wire:key="ai-agent-{{ $category['id'] }}"
                                >
                                    <button
                                        type="button"
                                        class="fi-ai-assistant-agent__trigger"
                                        wire:click="toggleCategory('{{ $category['id'] }}')"
                                        @disabled($processing)
                                    >
                                        <span class="fi-ai-assistant-agent__icon-wrap">
                                            <x-filament::icon
                                                :icon="$category['icon']"
                                                class="fi-ai-assistant-agent__icon"
                                            />
                                        </span>
                                        <span class="fi-ai-assistant-agent__label">{{ $category['label'] }}</span>
                                        <x-filament::icon
                                            icon="heroicon-m-chevron-down"
                                            @class([
                                                'fi-ai-assistant-agent__chevron',
                                                'fi-ai-assistant-agent__chevron--open' => $expandedCategory === $category['id'],
                                            ])
                                        />
                                    </button>

                                    @if ($expandedCategory === $category['id'])
                                        <ul class="fi-ai-assistant-agent__actions" role="list">
                                            @foreach ($category['actions'] as $action)
                                                <li wire:key="ai-action-{{ $category['id'] }}-{{ $loop->index }}">
                                                    <button
                                                        type="button"
                                                        class="fi-ai-assistant-agent__action"
                                                        wire:click="runAgentPrompt(@js($action['prompt']))"
                                                        wire:loading.attr="disabled"
                                                        wire:target="runAgentPrompt,sendMessage"
                                                        @disabled($processing)
                                                    >
                                                        {{ $action['label'] }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div
                        class="fi-ai-assistant-messages"
                        x-data="{ scrollToEnd() { this.$el.scrollTop = this.$el.scrollHeight } }"
                        x-on:ai-chat-scroll.window="scrollToEnd()"
                    >
                        @foreach ($messages as $message)
                            <div
                                wire:key="ai-msg-{{ $message->id }}"
                                @class([
                                    'fi-ai-assistant-bubble',
                                    'fi-ai-assistant-bubble--user' => $message->role === AiChatMessageRole::User,
                                    'fi-ai-assistant-bubble--assistant' => $message->role === AiChatMessageRole::Assistant,
                                ])
                            >
                                @if ($message->role === AiChatMessageRole::Assistant)
                                    <div class="fi-ai-assistant-bubble__text fi-ai-assistant-bubble__text--markdown">
                                        {!! \Illuminate\Support\Str::markdown($message->content, [
                                            'html_input' => 'strip',
                                            'allow_unsafe_links' => false,
                                        ]) !!}
                                    </div>
                                @else
                                    <div class="fi-ai-assistant-bubble__text">{{ $message->content }}</div>
                                @endif

                            </div>
                        @endforeach

                        @if ($processing)
                            <div class="fi-ai-assistant-thinking">
                                <x-filament::loading-indicator class="h-5 w-5" />
                                <span>Pensando…</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <x-slot name="footer">
            @if ($panel === 'conversation')
                <div class="fi-ai-assistant-footer">
                    <p class="fi-ai-assistant-footer__hint">
                        Mejores resultados si indicas nombres, números o IDs.
                    </p>

                    <form wire:submit="sendMessage" class="fi-ai-assistant-composer">
                        <textarea
                            wire:model="prompt"
                            rows="3"
                            class="fi-ai-assistant-composer__input"
                            placeholder="Pide una acción o pregunta…"
                            @disabled($processing)
                            x-on:keydown.enter.prevent="if (! $event.shiftKey) { $wire.sendMessage() }"
                        ></textarea>

                        <div class="fi-ai-assistant-composer__bar">
                            <span
                                wire:loading
                                wire:target="sendMessage,runAgentPrompt"
                                class="fi-ai-assistant-composer__status"
                            >
                                <x-filament::loading-indicator class="h-4 w-4" />
                                Enviando…
                            </span>
                            <span class="flex-1"></span>
                            <x-filament::icon-button
                                type="submit"
                                color="primary"
                                icon="heroicon-m-arrow-up"
                                label="Enviar"
                                wire:loading.attr="disabled"
                                wire:target="sendMessage,runAgentPrompt"
                                :disabled="$processing"
                                class="fi-ai-assistant-composer__send"
                            />
                        </div>
                    </form>
                </div>
            @else
                <div class="fi-ai-assistant-footer fi-ai-assistant-footer--list" aria-hidden="true"></div>
            @endif
        </x-slot>
    </x-filament::modal>
</div>