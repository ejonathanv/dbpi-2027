<?php

namespace Tests\Unit;

use App\Enums\AppModule;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\User;
use App\Support\Ai\AiCreateRecordActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCreateRecordActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_crm_account_from_confirmed_payload(): void
    {
        $user = User::factory()->create([
            'user_type' => UserType::Usuario,
            'is_active' => true,
        ]);

        $user->syncModulePermissions([
            AppModule::Crm->value => ModulePermissionLevel::Edit->value,
        ]);

        $result = app(AiCreateRecordActions::class)->execute($user, [
            'type' => 'create_crm_account',
            'payload' => ['name' => 'Confirmada por IA'],
        ]);

        $this->assertDatabaseHas('crm_accounts', ['name' => 'Confirmada por IA']);
        $this->assertNotEmpty($result['edit_url']);
    }
}
