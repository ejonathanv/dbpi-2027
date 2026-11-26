<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('name');
            $table->text('general_description')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('vat_treatment')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_form')->nullable();
            $table->string('cfdi_use')->nullable();
            $table->decimal('institutional_discount_percent', 5, 2)->default(0);
            $table->text('accounts_payable_info')->nullable();
            $table->string('account_type')->default('cliente');
            $table->string('sic_code', 20)->nullable();
            $table->string('industry')->nullable();
            $table->boolean('allows_partial_deliveries')->default(false);
            $table->string('delivery_days')->nullable();
            $table->text('delivery_schedule')->nullable();
            $table->decimal('advance_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('advance_days')->nullable();
            $table->decimal('on_delivery_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('on_delivery_days')->nullable();
            $table->decimal('installation_percent', 5, 2)->nullable();
            $table->unsignedSmallInteger('installation_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('second_last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('job_title')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('stage')->default('prospecto');
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->date('expected_close_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->foreignId('crm_account_id')->nullable()->constrained('crm_accounts')->nullOnDelete();
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->foreignId('crm_opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_calendar_events');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_accounts');
    }
};
