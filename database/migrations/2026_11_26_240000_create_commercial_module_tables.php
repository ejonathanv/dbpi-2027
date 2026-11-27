<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            'key' => 'exchange_rate_usd_mxn',
            'value' => '18.000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('commercial_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('crm_opportunity_id')->nullable()->constrained('crm_opportunities')->nullOnDelete();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->foreignId('crm_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->text('payment_terms')->nullable();
            $table->string('delivery_time')->nullable();
            $table->text('project_description')->nullable();
            $table->longText('technical_summary')->nullable();
            $table->longText('key_benefits')->nullable();
            $table->longText('observations')->nullable();
            $table->date('quoted_at');
            $table->string('presentation_currency');
            $table->decimal('exchange_rate', 12, 6)->default(0);
            $table->unsignedSmallInteger('validity_days')->default(30);
            $table->string('fob')->nullable();
            $table->decimal('general_discount_percent', 5, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->nullable();
            $table->boolean('vat_included')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commercial_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commercial_personnel', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commercial_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commercial_fups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('commercial_quote_id')->nullable()->constrained('commercial_quotes')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_fups');
        Schema::dropIfExists('commercial_suppliers');
        Schema::dropIfExists('commercial_personnel');
        Schema::dropIfExists('commercial_products');
        Schema::dropIfExists('commercial_quotes');
        Schema::dropIfExists('system_settings');
    }
};
