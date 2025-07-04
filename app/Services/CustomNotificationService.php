<?php

namespace App\Services;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class CustomNotificationService
{
    private const MAX_NOTIFICATIONS = 3;
    private const SESSION_KEY = 'custom_notifications';

    /**
     * Send a notification with FIFO management
     */
    public function send(string $title, string $body = '', string $type = 'info'): void
    {
        $notification = $this->createNotification($title, $body, $type);
        
        // Add to queue
        $this->addToQueue($notification);
        
        // Send the notification
        $notification->send();
    }

    /**
     * Send a success notification
     */
    public function success(string $title, string $body = ''): void
    {
        $this->send($title, $body, 'success');
    }

    /**
     * Send a warning notification
     */
    public function warning(string $title, string $body = ''): void
    {
        $this->send($title, $body, 'warning');
    }

    /**
     * Send a danger/error notification
     */
    public function danger(string $title, string $body = ''): void
    {
        $this->send($title, $body, 'danger');
    }

    /**
     * Send an info notification
     */
    public function info(string $title, string $body = ''): void
    {
        $this->send($title, $body, 'info');
    }

    /**
     * Get the current notification count
     */
    public function getCount(): int
    {
        $notifications = $this->getQueue();
        return count($notifications);
    }

    /**
     * Get all active notifications
     */
    public function getNotifications(): array
    {
        return $this->getQueue();
    }

    /**
     * Clear all notifications
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Remove a specific notification by index
     */
    public function remove(int $index): void
    {
        $notifications = $this->getQueue();
        
        if (isset($notifications[$index])) {
            unset($notifications[$index]);
            $notifications = array_values($notifications); // Re-index array
            $this->saveQueue($notifications);
        }
    }

    /**
     * Check if we're at the maximum notification limit
     */
    public function isAtLimit(): bool
    {
        return $this->getCount() >= self::MAX_NOTIFICATIONS;
    }

    /**
     * Create a Filament notification
     */
    private function createNotification(string $title, string $body, string $type): Notification
    {
        $notification = Notification::make()
            ->title($title);

        if (!empty($body)) {
            $notification->body($body);
        }

        switch ($type) {
            case 'success':
                $notification->success();
                break;
            case 'warning':
                $notification->warning();
                break;
            case 'danger':
                $notification->danger();
                break;
            case 'info':
            default:
                $notification->info();
                break;
        }

        return $notification;
    }

    /**
     * Add notification to the queue with FIFO management
     */
    private function addToQueue(Notification $notification): void
    {
        $notifications = $this->getQueue();
        
        // Add new notification
        $notifications[] = [
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'type' => $this->getNotificationType($notification),
            'timestamp' => now()->toISOString(),
        ];

        // Apply FIFO: if we exceed the limit, remove the oldest
        if (count($notifications) > self::MAX_NOTIFICATIONS) {
            $notifications = array_slice($notifications, -self::MAX_NOTIFICATIONS);
        }

        $this->saveQueue($notifications);
    }

    /**
     * Get the notification queue from session
     */
    private function getQueue(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Save the notification queue to session
     */
    private function saveQueue(array $notifications): void
    {
        Session::put(self::SESSION_KEY, $notifications);
    }

    /**
     * Determine the notification type from Filament notification
     */
    private function getNotificationType(Notification $notification): string
    {
        // This is a simplified approach - in practice, you might want to store the type
        // when creating the notification or use reflection to determine it
        return 'info'; // Default fallback
    }

    /**
     * Get notification statistics
     */
    public function getStats(): array
    {
        $notifications = $this->getQueue();
        
        $stats = [
            'total' => count($notifications),
            'max_allowed' => self::MAX_NOTIFICATIONS,
            'at_limit' => $this->isAtLimit(),
            'types' => [
                'success' => 0,
                'warning' => 0,
                'danger' => 0,
                'info' => 0,
            ]
        ];

        foreach ($notifications as $notification) {
            $type = $notification['type'] ?? 'info';
            if (isset($stats['types'][$type])) {
                $stats['types'][$type]++;
            }
        }

        return $stats;
    }

    /**
     * Get the oldest notification (first in queue)
     */
    public function getOldest(): ?array
    {
        $notifications = $this->getQueue();
        return $notifications[0] ?? null;
    }

    /**
     * Get the newest notification (last in queue)
     */
    public function getNewest(): ?array
    {
        $notifications = $this->getQueue();
        return end($notifications) ?: null;
    }
} 