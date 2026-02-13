<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationService;
use Livewire\Component;

class Bell extends Component
{
    public $unreadCount = 0;

    public $notifications;

    public $showDropdown = false;

    public function mount()
    {
        $this->loadUnreadCount();
    }

    public function loadUnreadCount()
    {
        $notificationService = app(NotificationService::class);
        $this->unreadCount = $notificationService->getUnreadCount(auth()->user());

        if ($this->showDropdown) {
            $this->notifications = $notificationService->getUnreadNotifications(auth()->user());
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = ! $this->showDropdown;

        if ($this->showDropdown) {
            $notificationService = app(NotificationService::class);
            $this->notifications = $notificationService->getUnreadNotifications(auth()->user());
        }
    }

    public function markAsRead($notificationId)
    {
        $notificationService = app(NotificationService::class);
        $notification = \App\Models\Notification::findOrFail($notificationId);

        // Verify ownership
        if ($notification->user_id !== auth()->id()) {
            return;
        }

        $notificationService->markAsRead($notification);
        $this->loadUnreadCount();
    }

    public function markAllAsRead()
    {
        $notificationService = app(NotificationService::class);
        $notificationService->markAllAsRead(auth()->user());
        $this->loadUnreadCount();
        $this->notifications = collect();
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
