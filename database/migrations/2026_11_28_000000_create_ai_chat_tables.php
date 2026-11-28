<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('idle');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->longText('content')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['ai_chat_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_conversations');
    }
};
