<?php

namespace App\Enums;

enum AiChatConversationStatus: string
{
    case Idle = 'idle';
    case Processing = 'processing';
    case Error = 'error';
}
