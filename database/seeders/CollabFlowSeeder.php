<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollabFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();

        if (!$user) {
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@collabflow.test',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]);
        }

        // Sample projects with different statuses
        $projects = [
            [
                'name' => 'Website Redesign',
                'description' => 'Modernize the company website with a fresh, clean design and improved user experience',
                'domain' => 'Design',
                'timeline' => '3 months',
                'status' => 'in_progress',
                'progress' => 75,
                'goals' => [
                    'Improve user engagement by 40%',
                    'Reduce bounce rate to under 30%',
                    'Implement modern design system'
                ],
                'kpis' => [
                    'Page load time under 2 seconds',
                    'Mobile responsive score 95+',
                    'Accessibility score A'
                ],
                'tasks' => [
                    ['name' => 'Research competitors and design trends', 'type' => 'human', 'status' => 'completed', 'hours' => 8],
                    ['name' => 'Create wireframes and mockups', 'type' => 'human', 'status' => 'completed', 'hours' => 16],
                    ['name' => 'Generate design system documentation', 'type' => 'ai', 'status' => 'completed', 'hours' => 4],
                    ['name' => 'Develop frontend components', 'type' => 'human', 'status' => 'in_progress', 'hours' => 40],
                    ['name' => 'Review and approve final design', 'type' => 'hitl', 'status' => 'pending', 'hours' => 2],
                    ['name' => 'Implement responsive layouts', 'type' => 'human', 'status' => 'pending', 'hours' => 24],
                    ['name' => 'Generate automated tests', 'type' => 'ai', 'status' => 'pending', 'hours' => 8],
                    ['name' => 'UAT and stakeholder review', 'type' => 'hitl', 'status' => 'pending', 'hours' => 8],
                ],
            ],
            [
                'name' => 'Mobile App Launch',
                'description' => 'Launch a new mobile application for iOS and Android to enhance customer engagement',
                'domain' => 'Technology',
                'timeline' => '6 months',
                'status' => 'active',
                'progress' => 45,
                'goals' => [
                    'Launch on both iOS and Android platforms',
                    'Achieve 10,000 downloads in first month',
                    'Maintain 4.5+ star rating'
                ],
                'kpis' => [
                    'App crash rate under 1%',
                    'User retention rate 60%+',
                    'Average session duration 5+ minutes'
                ],
                'tasks' => [
                    ['name' => 'Define app features and user flows', 'type' => 'human', 'status' => 'completed', 'hours' => 16],
                    ['name' => 'Create UI/UX designs', 'type' => 'human', 'status' => 'completed', 'hours' => 32],
                    ['name' => 'Set up development environment', 'type' => 'human', 'status' => 'completed', 'hours' => 8],
                    ['name' => 'Develop core features', 'type' => 'human', 'status' => 'in_progress', 'hours' => 80],
                    ['name' => 'Generate API documentation', 'type' => 'ai', 'status' => 'in_progress', 'hours' => 4],
                    ['name' => 'Code review and approval', 'type' => 'hitl', 'status' => 'pending', 'hours' => 8],
                    ['name' => 'Implement push notifications', 'type' => 'human', 'status' => 'pending', 'hours' => 16],
                    ['name' => 'Beta testing coordination', 'type' => 'hitl', 'status' => 'pending', 'hours' => 12],
                ],
            ],
            [
                'name' => 'AI Integration Project',
                'description' => 'Integrate AI features into the existing platform to automate workflows and improve efficiency',
                'domain' => 'AI & Machine Learning',
                'timeline' => '4 months',
                'status' => 'in_progress',
                'progress' => 90,
                'goals' => [
                    'Automate 50% of manual data entry tasks',
                    'Implement intelligent recommendations',
                    'Reduce processing time by 70%'
                ],
                'kpis' => [
                    'Model accuracy 95%+',
                    'API response time under 500ms',
                    'User satisfaction score 8.5/10'
                ],
                'tasks' => [
                    ['name' => 'Research AI/ML frameworks', 'type' => 'human', 'status' => 'completed', 'hours' => 12],
                    ['name' => 'Train initial ML models', 'type' => 'ai', 'status' => 'completed', 'hours' => 24],
                    ['name' => 'Validate model performance', 'type' => 'hitl', 'status' => 'completed', 'hours' => 8],
                    ['name' => 'Deploy models to production', 'type' => 'human', 'status' => 'completed', 'hours' => 16],
                    ['name' => 'Monitor and optimize performance', 'type' => 'ai', 'status' => 'in_progress', 'hours' => 8],
                    ['name' => 'Final approval for launch', 'type' => 'hitl', 'status' => 'pending', 'hours' => 2],
                ],
            ],
            [
                'name' => 'Marketing Campaign 2025',
                'description' => 'Comprehensive digital marketing campaign for Q1 2025 product launch',
                'domain' => 'Marketing',
                'timeline' => '2 months',
                'status' => 'planning',
                'progress' => 20,
                'goals' => [
                    'Generate 50,000 qualified leads',
                    'Increase brand awareness by 60%',
                    'Achieve 25% conversion rate'
                ],
                'kpis' => [
                    'Email open rate 30%+',
                    'Social media engagement rate 8%+',
                    'ROI 300%+'
                ],
                'tasks' => [
                    ['name' => 'Market research and analysis', 'type' => 'human', 'status' => 'in_progress', 'hours' => 16],
                    ['name' => 'Generate content ideas with AI', 'type' => 'ai', 'status' => 'pending', 'hours' => 4],
                    ['name' => 'Approve campaign strategy', 'type' => 'hitl', 'status' => 'pending', 'hours' => 4],
                    ['name' => 'Create marketing materials', 'type' => 'human', 'status' => 'pending', 'hours' => 40],
                ],
            ],
            [
                'name' => 'E-commerce Platform Upgrade',
                'description' => 'Upgrade the e-commerce platform with new features and improved performance',
                'domain' => 'Technology',
                'timeline' => '5 months',
                'status' => 'active',
                'progress' => 35,
                'goals' => [
                    'Improve checkout conversion by 25%',
                    'Add personalization features',
                    'Enhance mobile shopping experience'
                ],
                'kpis' => [
                    'Cart abandonment rate under 60%',
                    'Page load time under 1.5s',
                    'Mobile sales increase 40%'
                ],
                'tasks' => [
                    ['name' => 'Audit current platform', 'type' => 'human', 'status' => 'completed', 'hours' => 12],
                    ['name' => 'Design new checkout flow', 'type' => 'human', 'status' => 'completed', 'hours' => 20],
                    ['name' => 'Implement recommendation engine', 'type' => 'ai', 'status' => 'in_progress', 'hours' => 32],
                    ['name' => 'QA and user acceptance testing', 'type' => 'hitl', 'status' => 'pending', 'hours' => 16],
                ],
            ],
            [
                'name' => 'Customer Support Automation',
                'description' => 'Implement AI-powered chatbot and support ticket automation',
                'domain' => 'Customer Service',
                'timeline' => '3 months',
                'status' => 'draft',
                'progress' => 0,
                'goals' => [
                    'Reduce support response time by 80%',
                    'Handle 70% of queries automatically',
                    'Maintain customer satisfaction above 90%'
                ],
                'kpis' => [
                    'First response time under 30 seconds',
                    'Resolution rate 85%+',
                    'CSAT score 4.5/5'
                ],
                'tasks' => [
                    ['name' => 'Analyze support ticket patterns', 'type' => 'ai', 'status' => 'pending', 'hours' => 8],
                    ['name' => 'Design conversation flows', 'type' => 'human', 'status' => 'pending', 'hours' => 16],
                    ['name' => 'Review and approve flows', 'type' => 'hitl', 'status' => 'pending', 'hours' => 4],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $tasks = $projectData['tasks'];
            unset($projectData['tasks']);

            $project = Project::create([
                ...$projectData,
                'user_id' => $user->id,
            ]);

            foreach ($tasks as $taskData) {
                Task::create([
                    'project_id' => $project->id,
                    'name' => $taskData['name'],
                    'type' => $taskData['type'],
                    'status' => $taskData['status'],
                    'estimated_hours' => $taskData['hours'],
                    'description' => 'Task description for ' . $taskData['name'],
                ]);
            }
        }

        $this->command->info('CollabFlow seed data created successfully!');
        $this->command->info('Created ' . Project::count() . ' projects with ' . Task::count() . ' tasks.');
    }
}
