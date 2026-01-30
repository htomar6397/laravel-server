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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('theme_id')->nullable()->constrained();
            $table->foreignId('org_unit_id')->nullable()->constrained('organizational_units');
            $table->string('sector', 100)->nullable();
            $table->string('donor', 100)->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('currency', 3)->default('TZS');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['PLANNING', 'ACTIVE', 'SUSPENDED', 'COMPLETED', 'CANCELLED'])->default('PLANNING');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('geojson')->nullable();
            $table->text('location_description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['theme_id']);
            $table->index(['org_unit_id']);
            $table->index(['status']);
            $table->index(['created_by']);
            $table->index(['latitude', 'longitude']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
