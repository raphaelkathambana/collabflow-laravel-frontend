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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the old status column
            $table->dropColumn('status');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Recreate with blocked status included
            $table->enum('status', [
                'generated',
                'pending',
                'in_progress',
                'blocked',
                'review',
                'completed',
                'cancelled'
            ])->default('generated')->after('deliverables');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', [
                'generated',
                'pending',
                'in_progress',
                'review',
                'completed',
                'cancelled'
            ])->default('generated')->after('deliverables');
        });
    }
};
