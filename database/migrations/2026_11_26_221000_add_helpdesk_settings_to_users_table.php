<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_create_helpdesk_tickets')->default(true)->after('crm_record_scope');
            $table->boolean('helpdesk_auto_assign_on_create')->default(false)->after('can_create_helpdesk_tickets');
            $table->foreignId('helpdesk_employee_id')->nullable()->after('helpdesk_auto_assign_on_create')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('helpdesk_employee_id');
            $table->dropColumn([
                'can_create_helpdesk_tickets',
                'helpdesk_auto_assign_on_create',
            ]);
        });
    }
};
