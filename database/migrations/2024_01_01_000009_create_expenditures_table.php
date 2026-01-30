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
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('description', 255);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('TZS');
            $table->date('expenditure_date');
            $table->enum('category', ['PERSONNEL', 'EQUIPMENT', 'MATERIALS', 'SERVICES', 'TRAVEL', 'OVERHEAD', 'OTHER'])->default('OTHER');
            $table->string('subcategory', 100)->nullable();
            $table->string('vendor', 255)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'VERIFIED'])->default('PENDING');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->foreignId('entered_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
            $table->index(['expenditure_date']);
            $table->index(['category']);
            $table->index(['status']);
            $table->index(['approved_by']);
            $table->index(['verified_by']);
            $table->index(['entered_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};
