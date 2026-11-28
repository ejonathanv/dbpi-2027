<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Ai\AiHelpdeskTicketDraftBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiHelpdeskTicketDraftBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_ticket_without_crm_account(): void
    {
        $user = User::factory()->create();
        $builder = new AiHelpdeskTicketDraftBuilder;

        $result = $builder->build($user, [
            'subject' => 'Falla de servidor',
            'description' => 'Detalle',
            'helpdesk_category_id' => 1,
        ]);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('cuenta CRM', $result['content']);
    }
}
