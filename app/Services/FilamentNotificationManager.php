<?php

namespace App\Services;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Session;

class FilamentNotificationManager
{
    private const MAX_NOTIFICATIONS = 3;
    private const SESSION_KEY = 'filament_notification_queue';

    /**
     * Send a notification with automatic FIFO management
     */
    public function send(string $title, string $body = '', string $type = 'info'): void
    {
        // Remove oldest notification if we're at the limit
        $this->enforceLimit();
        
        // Create and send the notification
        $notification = $this->createNotification($title, $body, $type);
        $notification->send();
        
        // Add to our tracking queue
        $this->addToQueue($title, $body, $type);
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
     * Clear all notifications from the queue
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get current notification count
     */
    public function getCount(): int
    {
        return count($this->getQueue());
    }

    /**
     * Check if we're at the limit
     */
    public function isAtLimit(): bool
    {
        return $this->getCount() >= self::MAX_NOTIFICATIONS;
    }

    /**
     * Enforce the 3-notification limit by removing oldest
     */
    private function enforceLimit(): void
    {
        $queue = $this->getQueue();
        
        if (count($queue) >= self::MAX_NOTIFICATIONS) {
            // Remove the oldest notification (first in array)
            array_shift($queue);
            $this->saveQueue($queue);
        }
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
     * Add notification to tracking queue
     */
    private function addToQueue(string $title, string $body, string $type): void
    {
        $queue = $this->getQueue();
        
        $queue[] = [
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'timestamp' => now()->toISOString(),
        ];

        $this->saveQueue($queue);
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
    private function saveQueue(array $queue): void
    {
        Session::put(self::SESSION_KEY, $queue);
    }

    /**
     * Get notification statistics
     */
    public function getStats(): array
    {
        $queue = $this->getQueue();
        
        $stats = [
            'total' => count($queue),
            'max_allowed' => self::MAX_NOTIFICATIONS,
            'at_limit' => $this->isAtLimit(),
            'types' => [
                'success' => 0,
                'warning' => 0,
                'danger' => 0,
                'info' => 0,
            ]
        ];

        foreach ($queue as $notification) {
            $type = $notification['type'] ?? 'info';
            if (isset($stats['types'][$type])) {
                $stats['types'][$type]++;
            }
        }

        return $stats;
    }

    /**
     * Get all tracked notifications
     */
    public function getNotifications(): array
    {
        return $this->getQueue();
    }
} 