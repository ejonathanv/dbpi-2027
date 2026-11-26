<?php

use App\Models\CompanyPosition;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_position_id')
                ->nullable()
                ->after('salary_currency')
                ->constrained('company_positions')
                ->nullOnDelete();
        });

        Employee::query()
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->orderBy('id')
            ->each(function (Employee $employee): void {
                $name = trim((string) $employee->job_title);

                if ($name === '') {
                    return;
                }

                $position = CompanyPosition::query()->firstOrCreate(
                    [
                        'company_id' => $employee->company_id,
                        'name' => $name,
                    ],
                    ['is_active' => true],
                );

                $employee->forceFill(['company_position_id' => $position->getKey()])->saveQuietly();
            });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('salary_currency');
        });

        Employee::query()
            ->whereNotNull('company_position_id')
            ->with('position')
            ->orderBy('id')
            ->each(function (Employee $employee): void {
                $employee->forceFill([
                    'job_title' => $employee->position?->name,
                ])->saveQuietly();
            });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_position_id');
        });

        Schema::dropIfExists('company_positions');
    }
};
