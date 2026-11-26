<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('status');
            $table->string('salary_currency', 3)->default('MXN')->after('monthly_salary');
            $table->string('job_title')->nullable()->after('salary_currency');
            $table->foreignId('company_branch_id')->nullable()->after('job_title')->constrained('company_branches')->nullOnDelete();
            $table->foreignId('company_department_id')->nullable()->after('company_branch_id')->constrained('company_departments')->nullOnDelete();
            $table->string('bank_name')->nullable()->after('company_department_id');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_card_number', 50)->nullable()->after('bank_account_number');
            $table->string('bank_clabe', 18)->nullable()->after('bank_card_number');
            $table->decimal('imss_daily_sbc', 10, 2)->nullable()->after('bank_clabe');
            $table->text('payroll_discounts_notes')->nullable()->after('imss_daily_sbc');
            $table->unsignedSmallInteger('vacation_days_per_year')->nullable()->after('payroll_discounts_notes');
            $table->unsignedSmallInteger('vacation_days_used')->default(0)->after('vacation_days_per_year');
            $table->text('vacation_notes')->nullable()->after('vacation_days_used');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_branch_id');
            $table->dropConstrainedForeignId('company_department_id');
            $table->dropColumn([
                'monthly_salary',
                'salary_currency',
                'job_title',
                'bank_name',
                'bank_account_number',
                'bank_card_number',
                'bank_clabe',
                'imss_daily_sbc',
                'payroll_discounts_notes',
                'vacation_days_per_year',
                'vacation_days_used',
                'vacation_notes',
            ]);
        });
    }
};