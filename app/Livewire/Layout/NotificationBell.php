<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Notification Bell
 *
 * Header button displaying unread notification count with a red indicator dot.
 * Toggles the notifications drawer.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class NotificationBell extends Component
{
    /**
     * Re-render the unread dot when notifications change
     *
     * @return void
     */
    #[On('notifications-changed')]
    public function refresh(): void {}

    /**
     * Render the bell button with unread indicator
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.layout.notification-bell', [
            'unreadCount' => auth()->user()?->unreadNotifications()->count() ?? 0,
        ]);
    }
}
