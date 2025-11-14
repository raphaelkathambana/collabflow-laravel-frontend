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
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('goals')->nullable();
            $table->json('kpis')->nullable();
            $table->string('domain', 100)->nullable(); // e.g., Technology, Marketing, Design
            $table->string('timeline', 100)->nullable(); // e.g., "3 months", "6 weeks"
            $table->decimal('complexity_score', 3, 2)->nullable();
            $table->enum('status', ['draft', 'active', 'planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('draft');
            $table->integer('progress')->default(0); // 0-100 percentage
            $table->timestamps();
            $table->softDeletes();
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
