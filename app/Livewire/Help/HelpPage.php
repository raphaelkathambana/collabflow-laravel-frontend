<?php

namespace App\Livewire\Help;

use Livewire\Component;

class HelpPage extends Component
{
    public $searchQuery = '';
    public $expandedFaq = null;

    public function toggleFaq($faqId)
    {
        $this->expandedFaq = $this->expandedFaq === $faqId ? null : $faqId;
    }

    public function getFaqsProperty()
    {
        return [
            [
                'category' => 'Getting Started',
                'items' => [
                    [
                        'question' => 'How do I create a new project?',
                        'answer' => 'Click the "New Project" button in the sidebar or dashboard. Fill in the project details, define your goals and KPIs, and let AI generate initial tasks for you.',
                    ],
                    [
                        'question' => 'What is the difference between AI, Human, and HITL tasks?',
                        'answer' => 'AI tasks are generated and managed by our AI system. Human tasks require manual work from team members. HITL (Human-in-the-Loop) tasks need human approval or input at key checkpoints.',
                    ],
                    [
                        'question' => 'How do I invite team members?',
                        'answer' => 'Go to your project settings and use the "Manage Team" section to invite collaborators by email. They will receive an invitation to join your project.',
                    ],
                ],
            ],
            [
                'category' => 'Tasks & Projects',
                'items' => [
                    [
                        'question' => 'Can I edit tasks after they are created?',
                        'answer' => 'Yes, you can edit task details, reassign them, change their status, and update deadlines from the task detail view or the project dashboard.',
                    ],
                    [
                        'question' => 'How do I track project progress?',
                        'answer' => 'Use the Analytics tab in your project to see progress charts, task completion rates, and timeline tracking. The dashboard also shows a quick overview of all your projects.',
                    ],
                    [
                        'question' => 'What is the workflow visualization?',
                        'answer' => 'The Workflow tab shows a visual representation of your project tasks and their dependencies. You can drag tasks to rearrange them or use auto-layout options for horizontal or vertical arrangement.',
                    ],
                ],
            ],
            [
                'category' => 'Notifications & Schedule',
                'items' => [
                    [
                        'question' => 'How do I manage my notifications?',
                        'answer' => 'Click the bell icon in the header to view recent notifications. You can mark them as read, delete them, or view all notifications in the notification panel.',
                    ],
                    [
                        'question' => 'How do I use the Schedule page?',
                        'answer' => 'The Schedule page shows all your tasks and deadlines in calendar, timeline, or list view. You can filter by task type, project, or assignee to focus on what matters.',
                    ],
                    [
                        'question' => 'Can I set reminders for tasks?',
                        'answer' => 'Yes, you can set reminders for tasks with due dates. Reminders will be sent via email or push notifications based on your notification settings.',
                    ],
                ],
            ],
            [
                'category' => 'Account & Settings',
                'items' => [
                    [
                        'question' => 'How do I change my profile information?',
                        'answer' => 'Go to your Profile page and click "Edit Profile". Update your information and click "Save Changes" to apply the updates.',
                    ],
                    [
                        'question' => 'How do I enable two-factor authentication?',
                        'answer' => 'Visit the Settings page and toggle "Two-Factor Authentication" in the Security section. Follow the setup instructions to complete the process.',
                    ],
                    [
                        'question' => 'Can I change the theme?',
                        'answer' => 'Yes, click the sun/moon icon in the header to toggle between light and dark mode. Your preference will be saved automatically.',
                    ],
                ],
            ],
        ];
    }

    public function getFilteredFaqsProperty()
    {
        if (empty($this->searchQuery)) {
            return $this->faqs;
        }

        $searchLower = strtolower($this->searchQuery);

        return collect($this->faqs)->map(function ($category) use ($searchLower) {
            $filteredItems = collect($category['items'])->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['question']), $searchLower) ||
                       str_contains(strtolower($item['answer']), $searchLower);
            })->values()->toArray();

            return [
                'category' => $category['category'],
                'items' => $filteredItems,
            ];
        })->filter(function ($category) {
            return count($category['items']) > 0;
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.help.help-page', [
            'filteredFaqs' => $this->filteredFaqs,
        ]);
    }
}
