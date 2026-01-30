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
        Schema::create('data_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained();
            $table->decimal('value', 15, 2);
            $table->string('unit', 50)->nullable();
            $table->date('data_date');
            $table->enum('frequency', ['MONTHLY', 'QUARTERLY', 'ANNUALLY', 'ADHOC'])->default('QUARTERLY');
            $table->text('notes')->nullable();
            $table->string('data_source', 255)->nullable();
            $table->enum('verification_status', ['PENDING', 'VERIFIED', 'REJECTED'])->default('PENDING');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->foreignId('entered_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
            $table->index(['indicator_id']);
            $table->index(['data_date']);
            $table->index(['frequency']);
            $table->index(['verification_status']);
            $table->index(['verified_by']);
            $table->index(['entered_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_entries');
    }
};
