<?php

namespace App\Livewire\Layout;

use Livewire\Component;

/**
 * Notification Drawer
 *
 * Drawer displaying notifications with tabs for All, Enquiries, and Contracts.
 * Mounted directly under body to keep it within the Livewire tree.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @package App\Livewire
 */
class NotificationDrawer extends Component
{
    public string $tab = 'all';

    /**
     * Switch the active notification tab
     *
     * @param  string  $tab
     * @return void
     */
    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['all', 'enquiries', 'contracts'], true) ? $tab : 'all';
    }

    /**
     * Mark a single notification as read
     *
     * @param  string  $id
     * @return void
     */
    public function markAsRead(string $id): void
    {
        auth()->user()?->notifications()->whereKey($id)->first()?->markAsRead();
        $this->dispatch('notifications-changed');
    }

    /**
     * Mark all notifications as read
     *
     * @return void
     */
    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
        $this->dispatch('notifications-changed');
    }

    /**
     * Render the notification drawer
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $user = auth()->user();
        $all = $user ? $user->notifications()->limit(30)->get() : collect();

        $isEnquiry = function ($notification): bool {
            $cat = $notification->data['category'] ?? null;
            if ($cat === 'enquiries') {
                return true;
            }
            if ($cat === 'contracts') {
                return false;
            }

            return str_contains(strtolower($notification->type), 'enquiry')
                || str_contains(strtolower($notification->data['message'] ?? ''), 'enquiry');
        };

        $isContract = function ($notification): bool {
            $cat = $notification->data['category'] ?? null;
            if ($cat === 'contracts') {
                return true;
            }
            if ($cat === 'enquiries') {
                return false;
            }

            return str_contains(strtolower($notification->type), 'contract')
                || str_contains(strtolower($notification->data['message'] ?? ''), 'contract');
        };

        $enquiries = $all->filter($isEnquiry);
        $contracts = $all->filter($isContract);

        $notifications = match ($this->tab) {
            'enquiries' => $enquiries,
            'contracts' => $contracts,
            default => $all,
        };

        return view('livewire.layout.notification-drawer', [
            'notifications' => $notifications,
            'unreadCount' => $user ? $user->unreadNotifications()->count() : 0,
            'counts' => [
                'all' => $all->count(),
                'enquiries' => $enquiries->count(),
                'contracts' => $contracts->count(),
            ],
            'unreadCounts' => [
                'all' => $all->whereNull('read_at')->count(),
                'enquiries' => $enquiries->whereNull('read_at')->count(),
                'contracts' => $contracts->whereNull('read_at')->count(),
            ],
        ]);
    }
}
