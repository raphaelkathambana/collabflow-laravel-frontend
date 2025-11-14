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
        Schema::table('projects', function (Blueprint $table) {
            // Add new fields for UI redesign (start_date, end_date, workflow_state already exist)
            $table->integer('team_size')->nullable()->after('timeline');
            $table->json('reference_documents')->nullable()->after('goals');
            $table->text('success_metrics')->nullable()->after('reference_documents');
            $table->text('constraints')->nullable()->after('success_metrics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'team_size',
                'reference_documents',
                'success_metrics',
                'constraints'
            ]);
        });
    }
};
