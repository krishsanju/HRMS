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
            $table->text('rejection_reason')->nullable()->after('status');
            // Modifying enum requires doctrine/dbal. Assuming it's installed or compatible.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
            // Revert enum status to its previous state
            $table->enum('status', ['pending', 'approved'])->default('pending')->change();
        });
    }
};