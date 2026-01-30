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
        Schema::create('project_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('indicator_id')->nullable()->constrained();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['OUTPUT', 'OUTCOME', 'IMPACT'])->default('OUTPUT');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('achieved_value', 15, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->date('target_date')->nullable();
            $table->date('achieved_date')->nullable();
            $table->enum('status', ['PENDING', 'ACHIEVED', 'PARTIALLY_ACHIEVED', 'NOT_ACHIEVED'])->default('PENDING');
            $table->text('evidence')->nullable();
            $table->text('challenges')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
            $table->index(['indicator_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['reported_by']);
            $table->index(['target_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_results');
    }
};
