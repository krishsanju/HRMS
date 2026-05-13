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
            $table->text('reason')->after('to_date')->nullable(); // Add reason field for application
            $table->text('rejection_reason')->after('status')->nullable();
            $table->string('leave_type')->after('employee_id')->nullable(); // Add leave type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
            $table->dropColumn('reason');
            $table->dropColumn('leave_type');
        });
    }
};