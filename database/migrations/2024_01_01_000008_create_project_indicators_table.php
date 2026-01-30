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
        Schema::create('project_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained()->onDelete('cascade');
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            $table->enum('reporting_frequency', ['MONTHLY', 'QUARTERLY', 'ANNUALLY', 'ADHOC'])->default('QUARTERLY');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'indicator_id']);
            $table->index(['project_id']);
            $table->index(['indicator_id']);
            $table->index(['updated_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_indicators');
    }
};
