<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('message');
            $table->enum('type', ['INFO', 'SUCCESS', 'WARNING', 'ERROR', 'ALERT'])->default('INFO');
            $table->string('category', 50)->nullable(); // e.g., 'PROJECT', 'EXPENDITURE', 'SYSTEM'
            $table->string('action_url')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['is_read']);
            $table->index(['type']);
            $table->index(['category']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
