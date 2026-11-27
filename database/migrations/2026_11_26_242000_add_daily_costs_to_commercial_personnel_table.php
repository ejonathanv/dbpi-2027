<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_personnel', function (Blueprint $table) {
            $table->decimal('daily_cost_mxn', 12, 2)->default(0)->after('name');
            $table->decimal('daily_cost_usd', 12, 2)->default(0)->after('daily_cost_mxn');
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('commercial_personnel', function (Blueprint $table) {
            $table->string('role')->nullable()->after('name');
            $table->dropColumn(['daily_cost_mxn', 'daily_cost_usd']);
        });
    }
};
