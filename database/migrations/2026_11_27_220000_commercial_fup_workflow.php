<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->string('fup_number')->nullable()->after('id');
            $table->string('client_po_number')->nullable()->after('commercial_quote_id');
            $table->date('fup_date')->nullable()->after('client_po_number');
            $table->longText('observations_and_recommendations')->nullable()->after('fup_date');
            $table->date('commitment_delivery_date')->nullable();
            $table->date('commitment_installation_start_date')->nullable();
            $table->date('commitment_project_date')->nullable();
            $table->foreignId('account_executive_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('authorization_status')->default('draft')->after('account_executive_user_id');
            $table->json('frozen_snapshot')->nullable()->after('authorization_status');
            $table->timestamp('authorization_locked_at')->nullable()->after('frozen_snapshot');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        foreach (DB::table('commercial_fups')->orderBy('id')->get() as $row) {
            $label = (string) ($row->name ?? '');

            DB::table('commercial_fups')->where('id', $row->id)->update([
                'fup_number' => filled($label) ? $label : sprintf('FUP-LEGACY-%d', $row->id),
                'fup_date' => now()->toDateString(),
            ]);
        }

        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->unique('fup_number');
            $table->dropColumn('name');
        });

        Schema::create('commercial_quote_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_id')->constrained('commercial_quotes')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->string('file_path');
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('commercial_fup_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_fup_id')->constrained('commercial_fups')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->json('frozen_snapshot');
            $table->timestamp('requested_at');
            $table->timestamps();
        });

        Schema::create('commercial_fup_authorization_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_fup_authorization_request_id')
                ->constrained('commercial_fup_authorization_requests', indexName: 'cf_auth_reviews_request_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['commercial_fup_authorization_request_id', 'user_id'],
                'cf_auth_reviews_request_user_unique',
            );
        });

        Schema::create('commercial_fup_contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_fup_id')->constrained('commercial_fups')->cascadeOnDelete();
            $table->foreignId('commercial_quote_line_item_id')->constrained('commercial_quote_line_items')->cascadeOnDelete();
            $table->foreignId('helpdesk_contract_id')->constrained('helpdesk_contracts')->cascadeOnDelete();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['commercial_fup_id', 'commercial_quote_line_item_id'],
                'cf_contract_items_fup_line_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_fup_contract_items');
        Schema::dropIfExists('commercial_fup_authorization_reviews');
        Schema::dropIfExists('commercial_fup_authorization_requests');
        Schema::dropIfExists('commercial_quote_attachments');

        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->string('name')->nullable();
        });

        Schema::table('commercial_fups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_executive_user_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn([
                'fup_number',
                'client_po_number',
                'fup_date',
                'observations_and_recommendations',
                'commitment_delivery_date',
                'commitment_installation_start_date',
                'commitment_project_date',
                'authorization_status',
                'frozen_snapshot',
                'authorization_locked_at',
            ]);
        });
    }
};