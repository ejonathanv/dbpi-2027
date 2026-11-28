<?php

namespace Tests\Unit;

use App\Support\Ai\AiPendingActionPresenter;
use Tests\TestCase;

class AiPendingActionPresenterTest extends TestCase
{
    public function test_helpdesk_summary_uses_plain_language(): void
    {
        $action = [
            'type' => 'create_helpdesk_ticket',
            'label' => 'Crear ticket: Reparación',
            'payload' => [
                'subject' => 'Reparación de servidor',
                'description' => 'Falla crítica en el servidor del cliente.',
            ],
        ];

        $message = AiPendingActionPresenter::assistantMessage($action);

        $this->assertStringContainsString('Reparación de servidor', $message);
        $this->assertStringNotContainsString('opened_by_user_id', $message);
        $this->assertStringNotContainsString('{', $message);
    }
}
