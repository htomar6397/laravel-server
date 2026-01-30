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
        Schema::create('photo_captures', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->integer('file_size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->timestamp('captured_at')->nullable();
            $table->enum('status', ['ACTIVE', 'DELETED'])->default('ACTIVE');
            $table->foreignId('captured_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
            $table->index(['captured_by']);
            $table->index(['status']);
            $table->index(['captured_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_captures');
    }
};
