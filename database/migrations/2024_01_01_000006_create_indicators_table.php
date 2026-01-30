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
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('theme_id')->nullable()->constrained();
            $table->enum('type', ['QUANTITATIVE', 'QUALITATIVE'])->default('QUANTITATIVE');
            $table->string('unit', 50)->default('NUMBER');
            $table->string('unit_label', 50)->nullable();
            $table->decimal('baseline_value', 15, 2)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->enum('direction', ['INCREASE', 'DECREASE', 'MAINTAIN'])->default('INCREASE');
            $table->string('frequency', 50)->default('QUARTERLY');
            $table->string('data_source', 255)->nullable();
            $table->text('calculation_method')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['theme_id']);
            $table->index(['type']);
            $table->index(['frequency']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
