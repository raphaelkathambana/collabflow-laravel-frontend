<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert old string-based goals to new array format
     */
    public function up(): void
    {
        // Get all projects with string goals
        $projects = DB::table('projects')->whereNotNull('goals')->get();

        foreach ($projects as $project) {
            // Check if goals is already a valid JSON array
            $decoded = json_decode($project->goals, true);

            // If it's already an array with 'id', 'title', 'description', 'priority', skip it
            if (is_array($decoded) && isset($decoded[0]['id'])) {
                continue;
            }

            // Convert old string format to new array format
            $newGoals = [
                [
                    'id' => 0,
                    'title' => 'Primary Goal',
                    'description' => is_string($project->goals) ? $project->goals : (is_string($decoded) ? $decoded : 'Project goal'),
                    'priority' => 'medium'
                ]
            ];

            // Update the project
            DB::table('projects')
                ->where('id', $project->id)
                ->update([
                    'goals' => json_encode($newGoals)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get all projects with array goals
        $projects = DB::table('projects')->whereNotNull('goals')->get();

        foreach ($projects as $project) {
            $decoded = json_decode($project->goals, true);

            // If it's an array in new format, convert back to string
            if (is_array($decoded) && isset($decoded[0]['description'])) {
                $goalText = $decoded[0]['description'];

                DB::table('projects')
                    ->where('id', $project->id)
                    ->update([
                        'goals' => $goalText
                    ]);
            }
        }
    }
};
