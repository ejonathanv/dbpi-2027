<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('commercial_product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('commercial_products', function (Blueprint $table) {
            $table->string('part_number')->nullable()->after('name');
            $table->string('sat_code')->nullable()->after('part_number');
            $table->foreignId('commercial_product_category_id')
                ->nullable()
                ->after('sat_code')
                ->constrained('commercial_product_categories')
                ->nullOnDelete();
            $table->foreignId('commercial_product_type_id')
                ->nullable()
                ->after('commercial_product_category_id')
                ->constrained('commercial_product_types')
                ->nullOnDelete();
            $table->string('brand')->nullable()->after('commercial_product_type_id');
            $table->string('model')->nullable()->after('brand');
            $table->longText('specifications')->nullable()->after('model');
            $table->json('image_paths')->nullable()->after('specifications');
        });

        if (Schema::hasColumn('commercial_products', 'sku')) {
            DB::table('commercial_products')
                ->whereNotNull('sku')
                ->update(['part_number' => DB::raw('sku')]);

            Schema::table('commercial_products', function (Blueprint $table) {
                $table->dropColumn('sku');
            });
        }
    }

    public function down(): void
    {
        Schema::table('commercial_products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name');
            $table->dropConstrainedForeignId('commercial_product_category_id');
            $table->dropConstrainedForeignId('commercial_product_type_id');
            $table->dropColumn([
                'part_number',
                'sat_code',
                'brand',
                'model',
                'specifications',
                'image_paths',
            ]);
        });

        Schema::dropIfExists('commercial_product_types');
        Schema::dropIfExists('commercial_product_categories');
    }
};
