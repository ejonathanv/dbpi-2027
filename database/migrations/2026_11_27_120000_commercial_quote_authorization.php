<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_quotes', function (Blueprint $table) {
            $table->string('authorization_status')->default('draft')->after('created_by_user_id');
            $table->json('frozen_snapshot')->nullable()->after('authorization_status');
            $table->timestamp('authorization_locked_at')->nullable()->after('frozen_snapshot');
        });

        Schema::create('commercial_quote_authorizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('commercial_quote_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_id')->constrained('commercial_quotes')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->json('frozen_snapshot');
            $table->timestamp('requested_at');
            $table->timestamps();
        });

        Schema::create('commercial_quote_authorization_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_quote_authorization_request_id')
                ->constrained('commercial_quote_authorization_requests', indexName: 'cq_auth_reviews_request_id_foreign')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['commercial_quote_authorization_request_id', 'user_id'],
                'cq_auth_reviews_request_user_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_quote_authorization_reviews');
        Schema::dropIfExists('commercial_quote_authorization_requests');
        Schema::dropIfExists('commercial_quote_authorizers');

        Schema::table('commercial_quotes', function (Blueprint $table) {
            $table->dropColumn(['authorization_status', 'frozen_snapshot', 'authorization_locked_at']);
        });
    }
};
