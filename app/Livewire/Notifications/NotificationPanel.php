<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\On;

class NotificationPanel extends Component
{
    public $isOpen = false;
    public $filter = 'all'; // all, unread, read

    public $notifications = [];

    #[On('toggle-notifications')]
    public function togglePanel()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function mount()
    {
        // Mock notifications data
        $this->notifications = [
            [
                'id' => 1,
                'title' => 'Task Completed',
                'message' => 'Design System Setup has been completed',
                'timestamp' => '2 minutes ago',
                'read' => false,
                'type' => 'task',
            ],
            [
                'id' => 2,
                'title' => 'Project Update',
                'message' => 'Website Redesign is now 75% complete',
                'timestamp' => '1 hour ago',
                'read' => false,
                'type' => 'project',
            ],
            [
                'id' => 3,
                'title' => 'New Comment',
                'message' => 'Alice commented on Wireframe Review',
                'timestamp' => '3 hours ago',
                'read' => true,
                'type' => 'comment',
            ],
            [
                'id' => 4,
                'title' => 'HITL Checkpoint',
                'message' => 'Awaiting your approval on Design Review',
                'timestamp' => '5 hours ago',
                'read' => true,
                'type' => 'hitl',
            ],
        ];
    }


    public function closePanel()
    {
        $this->isOpen = false;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function markAsRead($id)
    {
        $index = collect($this->notifications)->search(fn($n) => $n['id'] == $id);
        if ($index !== false) {
            $this->notifications[$index]['read'] = !$this->notifications[$index]['read'];
        }
    }

    public function markAllAsRead()
    {
        foreach ($this->notifications as &$notification) {
            $notification['read'] = true;
        }
    }

    public function deleteNotification($id)
    {
        $this->notifications = collect($this->notifications)
            ->filter(fn($n) => $n['id'] != $id)
            ->values()
            ->toArray();
    }

    public function getFilteredNotificationsProperty()
    {
        return collect($this->notifications)->filter(function ($notification) {
            if ($this->filter === 'unread') {
                return !$notification['read'];
            } elseif ($this->filter === 'read') {
                return $notification['read'];
            }
            return true;
        })->values()->toArray();
    }

    public function getUnreadCountProperty()
    {
        return collect($this->notifications)->filter(fn($n) => !$n['read'])->count();
    }

    public function render()
    {
        return view('livewire.notifications.notification-panel', [
            'filteredNotifications' => $this->filteredNotifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}