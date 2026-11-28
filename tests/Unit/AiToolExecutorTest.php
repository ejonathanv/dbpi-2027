<?php

namespace Tests\Unit;

use App\Enums\AppModule;
use App\Enums\CrmOpportunityStage;
use App\Enums\CrmRecordScope;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\CrmAccount;
use App\Models\CrmOpportunity;
use App\Models\User;
use App\Support\Ai\AiReadTools;
use App\Support\Ai\AiToolExecutor;
use App\Support\Ai\AiUserContextBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolExecutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_denies_tool_not_in_enabled_list(): void
    {
        $user = User::factory()->create([
            'user_type' => UserType::Usuario,
            'is_active' => true,
        ]);

        $executor = new AiToolExecutor(new AiUserContextBuilder, new AiReadTools);

        $this->expectException(AuthorizationException::class);

        $executor->execute($user, 'search_crm_accounts', ['query' => 'test']);
    }

    public function test_opportunity_search_respects_own_scope(): void
    {
        $owner = User::factory()->create(['user_type' => UserType::Usuario, 'is_active' => true]);
        $other = User::factory()->create(['user_type' => UserType::Usuario, 'is_active' => true]);

        $owner->syncModulePermissions([
            AppModule::Crm->value => ModulePermissionLevel::View->value,
        ]);
        $owner->update(['crm_record_scope' => CrmRecordScope::Own]);

        $account = CrmAccount::query()->create(['name' => 'Cuenta scope test']);

        $mine = CrmOpportunity::query()->create([
            'name' => 'Oportunidad visible IA',
            'crm_account_id' => $account->getKey(),
            'assigned_user_id' => $owner->getKey(),
            'stage' => CrmOpportunityStage::Prospeccion,
        ]);

        CrmOpportunity::query()->create([
            'name' => 'Oportunidad ajena IA',
            'crm_account_id' => $account->getKey(),
            'assigned_user_id' => $other->getKey(),
            'stage' => CrmOpportunityStage::Prospeccion,
        ]);

        $executor = new AiToolExecutor(new AiUserContextBuilder, new AiReadTools);
        $result = $executor->execute($owner, 'search_crm_opportunities', ['query' => 'IA']);

        $this->assertStringContainsString((string) $mine->id, $result['content']);
        $this->assertStringNotContainsString('Oportunidad ajena', $result['content']);
    }
}
