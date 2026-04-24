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
            // Change the enum column to include 'cancelled'
            // This requires doctrine/dbal for older Laravel versions or specific database drivers.
            // For Laravel 12, it should handle it gracefully.
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Revert the enum column by removing 'cancelled'
            $table->enum('status', ['pending', 'approved', 'rejected'])->change();
        });
    }
};