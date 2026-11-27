<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('validity_number');
            $table->string('validity_unit');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('helpdesk_coverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('full_week')->default(true);
            $table->json('weekdays')->nullable();
            $table->boolean('all_day')->default(false);
            $table->time('coverage_start')->nullable();
            $table->time('coverage_end')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('helpdesk_service_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('helpdesk_service_agreement_severities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helpdesk_service_agreement_id')
                ->constrained('helpdesk_service_agreements')
                ->cascadeOnDelete();
            $table->string('severity');
            $table->unsignedInteger('response_number');
            $table->string('response_unit');
            $table->timestamps();

            $table->unique(['helpdesk_service_agreement_id', 'severity'], 'helpdesk_sla_severity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_service_agreement_severities');
        Schema::dropIfExists('helpdesk_service_agreements');
        Schema::dropIfExists('helpdesk_coverages');
        Schema::dropIfExists('helpdesk_contracts');
    }
};
