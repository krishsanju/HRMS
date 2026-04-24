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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Change the 'status' column to include 'cancelled' in its enum values
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Revert the 'status' column to its previous enum values
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};