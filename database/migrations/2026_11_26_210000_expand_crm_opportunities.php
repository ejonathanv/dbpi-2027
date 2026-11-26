<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->decimal('probability_percent', 5, 2)->nullable()->after('currency');
            $table->string('source')->nullable()->after('probability_percent');
            $table->string('fob')->nullable()->after('source');
            $table->boolean('use_account_vat')->default(true)->after('fob');
            $table->string('vat_treatment')->nullable()->after('use_account_vat');
            $table->string('estimated_delivery_time')->nullable()->after('vat_treatment');
            $table->boolean('use_account_payment_terms')->default(true)->after('estimated_delivery_time');
            $table->decimal('advance_percent', 5, 2)->nullable()->after('use_account_payment_terms');
            $table->unsignedSmallInteger('advance_days')->nullable()->after('advance_percent');
            $table->decimal('on_delivery_percent', 5, 2)->nullable()->after('advance_days');
            $table->unsignedSmallInteger('on_delivery_days')->nullable()->after('on_delivery_percent');
            $table->decimal('installation_percent', 5, 2)->nullable()->after('on_delivery_days');
            $table->unsignedSmallInteger('installation_days')->nullable()->after('installation_percent');
        });
    }

    public function down(): void
    {
        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'probability_percent',
                'source',
                'fob',
                'use_account_vat',
                'vat_treatment',
                'estimated_delivery_time',
                'use_account_payment_terms',
                'advance_percent',
                'advance_days',
                'on_delivery_percent',
                'on_delivery_days',
                'installation_percent',
                'installation_days',
            ]);
        });
    }
};
