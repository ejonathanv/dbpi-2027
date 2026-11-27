<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_quote_line_items', function (Blueprint $table) {
            $table->string('manual_margin_override_type')->nullable()->after('customer_discount_percent');
            $table->decimal('manual_margin_override_value', 14, 2)->nullable()->after('manual_margin_override_type');
        });
    }

    public function down(): void
    {
        Schema::table('commercial_quote_line_items', function (Blueprint $table) {
            $table->dropColumn(['manual_margin_override_type', 'manual_margin_override_value']);
        });
    }
};
