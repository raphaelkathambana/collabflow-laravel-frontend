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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Notification Settings
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(false);
            $table->boolean('weekly_digest')->default(true);
            $table->boolean('task_reminders')->default(true);
            $table->boolean('project_updates')->default(true);

            // Privacy Settings
            $table->boolean('profile_visibility')->default(true);
            $table->boolean('data_collection')->default(true);

            // Preferences
            $table->string('timezone')->default('UTC');
            $table->string('language')->default('en');
            $table->string('date_format')->default('M d, Y');
            $table->string('time_format')->default('12h');

            $table->timestamps();

            // Unique constraint - one settings record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
