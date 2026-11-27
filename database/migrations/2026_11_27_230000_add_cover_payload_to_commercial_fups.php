<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->json('cover_payload')->nullable()->after('commitment_project_date');
        });
    }

    public function down(): void
    {
        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->dropColumn('cover_payload');
        });
    }
};