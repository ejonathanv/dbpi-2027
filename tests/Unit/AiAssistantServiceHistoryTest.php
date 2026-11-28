<?php

namespace Tests\Unit;

use App\Enums\AiChatMessageRole;
use App\Models\AiChatConversation;
use App\Models\AiChatMessage;
use App\Models\User;
use App\Support\Ai\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AiAssistantServiceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_api_messages_orders_oldest_user_first(): void
    {
        $user = User::factory()->create();
        $conversation = AiChatConversation::query()->create(['user_id' => $user->getKey()]);

        AiChatMessage::query()->create([
            'ai_chat_conversation_id' => $conversation->getKey(),
            'role' => AiChatMessageRole::User,
            'content' => 'primero',
        ]);
        AiChatMessage::query()->create([
            'ai_chat_conversation_id' => $conversation->getKey(),
            'role' => AiChatMessageRole::Assistant,
            'content' => 'respuesta uno',
        ]);
        AiChatMessage::query()->create([
            'ai_chat_conversation_id' => $conversation->getKey(),
            'role' => AiChatMessageRole::User,
            'content' => 'segundo',
        ]);

        $service = $this->app->make(AiAssistantService::class);
        $method = new ReflectionMethod(AiAssistantService::class, 'buildApiMessages');
        $method->setAccessible(true);

        /** @var array<int, array<string, mixed>> $messages */
        $messages = $method->invoke($service, $conversation, $user);

        $roles = array_column(array_slice($messages, 1), 'role');
        $contents = array_column(array_slice($messages, 1), 'content');

        $this->assertSame(['user', 'assistant', 'user'], $roles);
        $this->assertSame(['primero', 'respuesta uno', 'segundo'], $contents);
    }
}
