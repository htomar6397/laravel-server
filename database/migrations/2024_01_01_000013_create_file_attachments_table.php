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
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->integer('file_size');
            $table->string('file_path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('entity_type', 100); // e.g., 'Project', 'Expenditure'
            $table->foreignId('entity_id');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_type']);
            $table->index(['entity_id']);
            $table->index(['uploaded_by']);
            $table->index(['mime_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
