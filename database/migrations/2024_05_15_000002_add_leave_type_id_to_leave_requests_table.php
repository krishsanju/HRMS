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
            $table->foreignId('leave_type_id')
                  ->nullable() // Can be null if a default leave type is not set or for legacy data
                  ->after('employee_id')
                  ->constrained('leave_types')
                  ->onDelete('set null'); // If a leave type is deleted, set to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leave_type_id');
        });
    }
};