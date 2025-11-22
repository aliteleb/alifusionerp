<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BrowserPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?int $facilityId = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $title,
        public string $body,
        public ?string $icon = null,
        public ?string $url = null,
        ?int $facilityId = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->icon($this->icon ?? (settings('logo') ?: asset('images/logo.png')))
            ->badge(settings('logo') ?: asset('images/logo.png'))
            ->data(['url' => $this->url ?? url()->current()])
            ->options(['TTL' => 3600]); // الإشعار يعيش ساعة
    }
}
