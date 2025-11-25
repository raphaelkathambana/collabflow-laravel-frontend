<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\On;

class NotificationPanel extends Component
{
    public $isOpen = false;
    public $filter = 'all'; // all, unread, read

    #[On('toggle-notifications')]
    public function togglePanel()
    {
        $this->isOpen = !$this->isOpen;

        // Refresh notifications when panel opens
        if ($this->isOpen) {
            $this->dispatch('$refresh');
        }
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
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            if ($notification->read_at) {
                $notification->markAsUnread();
            } else {
                $notification->markAsRead();
            }
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification($id)
    {
        auth()->user()->notifications()->find($id)?->delete();
    }

    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->take(50)->get();
    }

    public function getUnreadCountProperty()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function render()
    {
        return view('livewire.notifications.notification-panel', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
