<?php

namespace MaherElGamil\Periscope\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class WebhookChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        $url = method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('webhook', $notification)
            : null;

        if (! is_string($url) || $url === '') {
            return;
        }

        $payload = method_exists($notification, 'toWebhook')
            ? $notification->toWebhook($notifiable)
            : (method_exists($notification, 'toArray') ? $notification->toArray($notifiable) : []);

        Http::asJson()
            ->acceptJson()
            ->timeout(10)
            ->post($url, $payload);
    }
}
