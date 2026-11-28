<?php

namespace App\Jobs;

use App\Support\Ai\AiAssistantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAiChatTurn implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $conversationId,
    ) {}

    public function handle(AiAssistantService $assistantService): void
    {
        $assistantService->processTurn($this->conversationId);
    }
}
