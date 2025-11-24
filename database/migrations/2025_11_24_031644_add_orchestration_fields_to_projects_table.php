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
            if (!Schema::hasColumn('projects', 'status')) {
                $table->string('status', 50)->default('draft')->after('context');
            }
            if (!Schema::hasColumn('projects', 'n8n_execution_id')) {
                $table->string('n8n_execution_id')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['status', 'n8n_execution_id']);
        });
    }
};
