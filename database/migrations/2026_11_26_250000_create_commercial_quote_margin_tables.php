<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_quote_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_id')->constrained('commercial_quotes')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('hide_title_in_quote')->default(false);
            $table->boolean('hide_group_in_quote')->default(false);
            $table->boolean('hide_items_in_quote')->default(false);
            $table->boolean('exclude_from_margin')->default(false);
            $table->timestamps();
        });

        Schema::create('commercial_quote_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_group_id')->constrained('commercial_quote_groups')->cascadeOnDelete();
            $table->string('type');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreignId('commercial_product_id')->nullable()->constrained('commercial_products')->nullOnDelete();
            $table->string('product_alias')->nullable();
            $table->boolean('show_part_number')->default(true);
            $table->decimal('product_quantity', 12, 4)->default(1);
            $table->boolean('show_product_image')->default(true);
            $table->boolean('hide_product_in_quote')->default(false);
            $table->boolean('product_exclude_from_margin')->default(false);
            $table->foreignId('commercial_supplier_id')->nullable()->constrained('commercial_suppliers')->nullOnDelete();
            $table->decimal('supplier_cost', 14, 2)->default(0);
            $table->decimal('supplier_discount_percent', 5, 2)->default(0);
            $table->decimal('supplier_special_discount_percent', 5, 2)->default(0);
            $table->decimal('customer_discount_percent', 5, 2)->default(0);

            $table->foreignId('commercial_personnel_id')->nullable()->constrained('commercial_personnel')->nullOnDelete();
            $table->decimal('personnel_quantity', 12, 4)->default(1);
            $table->unsignedSmallInteger('personnel_days')->default(1);

            $table->foreignId('helpdesk_contract_id')->nullable()->constrained('helpdesk_contracts')->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('commercial_quote_line_item_additional_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_line_item_id')
                ->constrained('commercial_quote_line_items')
                ->cascadeOnDelete();
            $table->string('concept');
            $table->decimal('amount', 14, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_quote_line_item_additional_costs');
        Schema::dropIfExists('commercial_quote_line_items');
        Schema::dropIfExists('commercial_quote_groups');
    }
};
