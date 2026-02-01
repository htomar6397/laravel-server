<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing data to new category values
        DB::table('expenditures')->where('category', 'PERSONNEL')->update(['category' => 'Labor']);
        DB::table('expenditures')->where('category', 'EQUIPMENT')->update(['category' => 'Equipment']);
        DB::table('expenditures')->where('category', 'MATERIALS')->update(['category' => 'Materials']);
        DB::table('expenditures')->where('category', 'SERVICES')->update(['category' => 'Services']);
        DB::table('expenditures')->where('category', 'TRAVEL')->update(['category' => 'Transport']);
        DB::table('expenditures')->where('category', 'OVERHEAD')->update(['category' => 'Other']);
        DB::table('expenditures')->where('category', 'OTHER')->update(['category' => 'Other']);

        // Change column type from enum to string
        Schema::table('expenditures', function (Blueprint $table) {
            $table->string('category', 50)->default('Other')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data to old category values
        DB::table('expenditures')->where('category', 'Labor')->update(['category' => 'PERSONNEL']);
        DB::table('expenditures')->where('category', 'Equipment')->update(['category' => 'EQUIPMENT']);
        DB::table('expenditures')->where('category', 'Materials')->update(['category' => 'MATERIALS']);
        DB::table('expenditures')->where('category', 'Services')->update(['category' => 'SERVICES']);
        DB::table('expenditures')->where('category', 'Transport')->update(['category' => 'TRAVEL']);
        DB::table('expenditures')->where('category', 'Other')->update(['category' => 'OTHER']);

        // Change column back to enum
        Schema::table('expenditures', function (Blueprint $table) {
            DB::statement("ALTER TABLE expenditures MODIFY category ENUM('PERSONNEL', 'EQUIPMENT', 'MATERIALS', 'SERVICES', 'TRAVEL', 'OVERHEAD', 'OTHER') DEFAULT 'OTHER'");
        });
    }
};
