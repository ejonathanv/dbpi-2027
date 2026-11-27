<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('helpdesk_categories')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('depth')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_account_id')->constrained('crm_accounts')->cascadeOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->dateTime('opened_at');
            $table->string('opened_by_type');
            $table->foreignId('opened_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opened_by_contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            $table->foreignId('helpdesk_category_id')->constrained('helpdesk_categories')->restrictOnDelete();
            $table->string('status')->default('abierto');
            $table->string('severity')->default('normal');
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('company_department_id')->constrained('company_departments')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('helpdesk_ticket_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helpdesk_ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->text('description');
            $table->unsignedTinyInteger('work_hours');
            $table->unsignedTinyInteger('work_minutes')->default(0);
            $table->string('attention_type');
            $table->unsignedTinyInteger('travel_hours')->nullable();
            $table->unsignedTinyInteger('travel_minutes')->nullable();
            $table->string('visibility')->default('privado');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('helpdesk_ticket_event_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('helpdesk_ticket_event_id')->constrained('helpdesk_ticket_events')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_event_attachments');
        Schema::dropIfExists('helpdesk_ticket_events');
        Schema::dropIfExists('helpdesk_tickets');
        Schema::dropIfExists('helpdesk_categories');
    }
};
