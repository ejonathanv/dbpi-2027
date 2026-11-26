<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->boolean('use_account_delivery_days')->default(true)->after('estimated_delivery_time');
            $table->string('delivery_days')->nullable()->after('use_account_delivery_days');
            $table->text('delivery_schedule')->nullable()->after('delivery_days');
            $table->boolean('allows_partial_deliveries')->nullable()->after('delivery_schedule');
        });
    }

    public function down(): void
    {
        Schema::table('crm_opportunities', function (Blueprint $table) {
            $table->dropColumn([
                'use_account_delivery_days',
                'delivery_days',
                'delivery_schedule',
                'allows_partial_deliveries',
            ]);
        });
    }
};
