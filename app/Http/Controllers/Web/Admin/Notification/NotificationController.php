<?php

namespace App\Http\Controllers\Web\Admin\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Find notification by id and make sure it belongs to the current user
     */
    private function findNotification(string $id): ?DatabaseNotification
    {
        return auth()->user()->notifications()->find($id);
    }

    /**
     * Get all notifications (read and unread)
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return $this->successResponse($notifications, 'All notifications retrieved');
    }

    /**
     * Get only unread notifications
     */
    public function unread()
    {
        $notifications = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->paginate(20);

        return $this->successResponse($notifications, 'Unread notifications retrieved');
    }

    /**
     * Mark one notification as read
     */
    public function markAsRead(string $id)
    {
        $notification = $this->findNotification($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return $this->successResponse(null, 'Notification marked as read');
    }

    /**
     * Mark all unread notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->successResponse(null, 'All notifications marked as read');
    }

    /**
     * Mark one notification as unread
     */
    public function markAsUnread(string $id)
    {
        $notification = $this->findNotification($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        $notification->update(['read_at' => null]);

        return $this->successResponse(null, 'Notification marked as unread');
    }

    /**
     * Delete one notification
     */
    public function destroy(string $id)
    {
        $notification = $this->findNotification($id);

        if (!$notification) {
            return $this->errorResponse('Notification not found', null, 404);
        }

        $notification->delete();

        return $this->successResponse(null, 'Notification deleted');
    }

    /**
     * Delete all notifications
     */
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return $this->successResponse(null, 'All notifications deleted');
    }
}
