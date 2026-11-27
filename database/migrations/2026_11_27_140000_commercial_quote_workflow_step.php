<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_quotes', function (Blueprint $table) {
            $table->string('workflow_step')->default('in_progress')->after('authorization_locked_at');
        });

        DB::table('commercial_quotes')
            ->where('authorization_status', 'pending_authorization')
            ->update(['workflow_step' => 'waiting_internal_authorization']);

        DB::table('commercial_quotes')
            ->where('authorization_status', 'authorized')
            ->update(['workflow_step' => 'internally_authorized']);

        DB::table('commercial_quotes')
            ->where('authorization_status', 'changes_requested')
            ->update(['workflow_step' => 'negotiation_review']);
    }

    public function down(): void
    {
        Schema::table('commercial_quotes', function (Blueprint $table) {
            $table->dropColumn('workflow_step');
        });
    }
};