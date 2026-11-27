<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_account_helpdesk_contract', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->foreignId('helpdesk_contract_id')->constrained('helpdesk_contracts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['crm_account_id', 'helpdesk_contract_id'], 'crm_account_helpdesk_contract_unique');
        });

        Schema::create('crm_account_helpdesk_coverage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->foreignId('helpdesk_coverage_id')->constrained('helpdesk_coverages')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['crm_account_id', 'helpdesk_coverage_id'], 'crm_account_helpdesk_coverage_unique');
        });

        Schema::create('crm_account_helpdesk_service_agreement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->foreignId('helpdesk_service_agreement_id')->constrained('helpdesk_service_agreements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['crm_account_id', 'helpdesk_service_agreement_id'],
                'crm_account_helpdesk_sla_unique'
            );
        });

        $now = now();

        foreach (DB::table('helpdesk_contracts')->select('id', 'crm_account_id')->get() as $row) {
            DB::table('crm_account_helpdesk_contract')->insert([
                'crm_account_id' => $row->crm_account_id,
                'helpdesk_contract_id' => $row->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('helpdesk_coverages')->select('id', 'crm_account_id')->get() as $row) {
            DB::table('crm_account_helpdesk_coverage')->insert([
                'crm_account_id' => $row->crm_account_id,
                'helpdesk_coverage_id' => $row->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (DB::table('helpdesk_service_agreements')->select('id', 'crm_account_id')->get() as $row) {
            DB::table('crm_account_helpdesk_service_agreement')->insert([
                'crm_account_id' => $row->crm_account_id,
                'helpdesk_service_agreement_id' => $row->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('helpdesk_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_account_id');
        });

        Schema::table('helpdesk_coverages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_account_id');
        });

        Schema::table('helpdesk_service_agreements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('helpdesk_contracts', function (Blueprint $table) {
            $table->foreignId('crm_account_id')->nullable()->after('id')->constrained('crm_accounts')->cascadeOnDelete();
        });

        Schema::table('helpdesk_coverages', function (Blueprint $table) {
            $table->foreignId('crm_account_id')->nullable()->after('id')->constrained('crm_accounts')->cascadeOnDelete();
        });

        Schema::table('helpdesk_service_agreements', function (Blueprint $table) {
            $table->foreignId('crm_account_id')->nullable()->after('id')->constrained('crm_accounts')->cascadeOnDelete();
        });

        foreach (DB::table('crm_account_helpdesk_contract')->get() as $pivot) {
            DB::table('helpdesk_contracts')
                ->where('id', $pivot->helpdesk_contract_id)
                ->update(['crm_account_id' => $pivot->crm_account_id]);
        }

        foreach (DB::table('crm_account_helpdesk_coverage')->get() as $pivot) {
            DB::table('helpdesk_coverages')
                ->where('id', $pivot->helpdesk_coverage_id)
                ->update(['crm_account_id' => $pivot->crm_account_id]);
        }

        foreach (DB::table('crm_account_helpdesk_service_agreement')->get() as $pivot) {
            DB::table('helpdesk_service_agreements')
                ->where('id', $pivot->helpdesk_service_agreement_id)
                ->update(['crm_account_id' => $pivot->crm_account_id]);
        }

        Schema::dropIfExists('crm_account_helpdesk_service_agreement');
        Schema::dropIfExists('crm_account_helpdesk_coverage');
        Schema::dropIfExists('crm_account_helpdesk_contract');
    }
};
