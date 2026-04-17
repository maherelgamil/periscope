<?php

namespace MaherElGamil\Periscope\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use MaherElGamil\Periscope\Alerts\Alert;

class PeriscopeAlert extends Notification
{
    use Queueable;

    public function __construct(public Alert $alert) {}

    public function via(mixed $notifiable): array
    {
        return (array) config('periscope.alerts.channels', []);
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Periscope] {$this->alert->title}")
            ->line($this->alert->message);
    }

    public function toSlack(mixed $notifiable): SlackMessage
    {
        $color = match ($this->alert->severity) {
            'error' => 'danger',
            'warning' => 'warning',
            default => 'good',
        };

        return (new SlackMessage)
            ->error()
            ->content("*{$this->alert->title}*\n{$this->alert->message}");
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'key' => $this->alert->key,
            'title' => $this->alert->title,
            'message' => $this->alert->message,
            'severity' => $this->alert->severity,
            'context' => $this->alert->context,
        ];
    }
}
