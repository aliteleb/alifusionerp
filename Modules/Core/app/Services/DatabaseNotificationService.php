<?php

namespace Modules\Core\Services;

use Modules\Core\Enums\NotificationStatus;
use Modules\Core\Enums\NotificationType;
use Modules\Core\Entities\User;
use App\Notifications\BrowserPushNotification;
use App\Notifications\QueuedDatabaseNotification;
use App\Notifications\SynchronousDatabaseNotification;
use BackedEnum;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseNotificationService
{
    protected ?int $facilityId = null;

    protected ?int $branchId = null;

    protected string $title = '';

    protected string $body = '';

    protected array $bodyData = [];

    protected NotificationStatus $status = NotificationStatus::Info;

    protected ?NotificationType $type = null;

    protected string|BackedEnum $icon = Heroicon::Bell;

    protected array $actions = [];

    protected ?Collection $users = null;

    protected bool $useQueue = false;

    protected ?string $queueName = null;

    protected bool $useBulk = false;

    protected bool $sendBrowserPush = true;

    protected ?string $actionUrl = null;

    public function __construct()
    {
        $this->facilityId = TenantDatabaseService::getCurrentFacility()?->id;
    }

    public static function make(): self
    {
        return new self;
    }

    public function title(string $title): self
    {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Notification title cannot be empty');
        }

        $this->title = $title;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $bodyData
     */
    public function description(string $body, array $bodyData = []): self
    {
        $this->body = $body;
        $this->bodyData = $bodyData;

        return $this;
    }

    public function icon(string|BackedEnum $icon): self
    {
        $value = $icon instanceof BackedEnum
            ? ($icon instanceof Heroicon
                ? 'heroicon-o-'.$icon->value
                : (string) $icon->value)
            : $icon;

        if (trim((string) $value) === '') {
            throw new \InvalidArgumentException('Notification icon cannot be empty');
        }

        $this->icon = $value;

        return $this;
    }

    public function status(NotificationStatus|string $status): self
    {
        $this->status = $status instanceof NotificationStatus
            ? $status
            : match (strtolower($status)) {
                'success' => NotificationStatus::Success,
                'warning' => NotificationStatus::Warning,
                'danger', 'error' => NotificationStatus::Danger,
                default => NotificationStatus::Info,
            };

        return $this;
    }

    public function type(?NotificationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param  Collection<int, User>|array<int, User>|array<int, int>  $users
     */
    public function users(Collection|array $users): self
    {
        if ($users instanceof Collection) {
            if ($users->isEmpty()) {
                throw new \InvalidArgumentException('Users collection cannot be empty');
            }

            $this->users = $users;

            return $this;
        }

        if ($users === []) {
            throw new \InvalidArgumentException('Users array cannot be empty');
        }

        $first = reset($users);

        if (is_int($first)) {
            $resolved = User::whereIn('id', $users)->get();

            if ($resolved->isEmpty()) {
                throw new \InvalidArgumentException('No users found with the provided IDs');
            }

            $this->users = $resolved;
        } else {
            $this->users = collect($users);
        }

        return $this;
    }

    public function toBranch(int $branchId): self
    {
        if ($branchId <= 0) {
            throw new \InvalidArgumentException('Branch ID must be a positive integer');
        }

        $this->branchId = $branchId;

        return $this;
    }

    public function toFacility(int $facilityId): self
    {
        if ($facilityId <= 0) {
            throw new \InvalidArgumentException('Facility ID must be a positive integer');
        }

        $this->facilityId = $facilityId;

        return $this;
    }

    /**
     * @param  array<int, \Filament\Actions\Action>  $actions
     */
    public function actions(array $actions = []): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function onQueue(?string $queue = null): self
    {
        $this->useQueue = true;
        $this->queueName = $queue;

        return $this;
    }

    public function bulk(bool $enable = true): self
    {
        $this->useBulk = $enable;

        return $this;
    }

    public function browserPush(bool $enable = true): self
    {
        $this->sendBrowserPush = $enable;

        return $this;
    }

    public function url(?string $url): self
    {
        $this->actionUrl = $url;

        return $this;
    }

    public function send(): void
    {
        $users = $this->getUsers();

        if ($users->isEmpty()) {
            return;
        }

        $this->notifyRecipients(
            $users,
            function (): void {
                if ($this->branchId) {
                    Context::add('branch_id_for_notification', $this->branchId);
                }
            },
            function (): void {
                if ($this->branchId) {
                    Context::forget('branch_id_for_notification');
                }
            }
        );
    }

    public static function sendToBranch(
        int $branchId,
        string $title,
        string $body,
        array $bodyData = [],
        string $status = 'success',
        string|\BackedEnum $icon = Heroicon::Bell,
        array $actions = [],
        mixed $creator = null
    ): void {
        self::make()
            ->title($title)
            ->description($body, $bodyData)
            ->status($status)
            ->icon($icon)
            ->toBranch($branchId)
            ->actions($actions)
            ->send();
    }

    public static function sendCreatedNotification(
        mixed $record,
        string $modelType,
        string $titleField,
        string|\BackedEnum $icon,
        string $status = 'success',
        ?string $resourceClass = null
    ): void {
        $title = __(':modelType Created', ['modelType' => __($modelType)]);
        $body = self::generateNotificationBody($modelType, $record->$titleField);

        self::sendNotificationWithLayout(
            branchId: $record->branch_id,
            title: $title,
            body: $body,
            bodyData: [
                $titleField => $record->$titleField,
                'creator' => self::getCreatorName(),
            ],
            status: $status,
            icon: $icon,
            resourceClass: $resourceClass,
            record: $record
        );
    }

    public static function sendGeneralNotification(
        mixed $record,
        string $modelType,
        string $action,
        string $titleField,
        string|\BackedEnum $icon,
        string $status = 'info',
        ?string $resourceClass = null,
        array $additionalData = []
    ): void {
        $title = __(':modelType | :action', ['modelType' => __($modelType), 'action' => __($action)]);
        $body = self::generateGeneralNotificationBody($modelType, $action, $record->$titleField, $additionalData);

        self::sendNotificationWithLayout(
            branchId: $record->branch_id,
            title: $title,
            body: $body,
            bodyData: array_merge([
                $titleField => $record->$titleField,
                'creator' => self::getCreatorName(),
            ], $additionalData),
            status: $status,
            icon: $icon,
            resourceClass: $resourceClass,
            record: $record
        );
    }

    protected function notifyRecipients(
        Collection $recipients,
        ?callable $beforeSend = null,
        ?callable $afterSend = null
    ): void {
        if ($recipients->isEmpty()) {
            return;
        }

        if ($beforeSend) {
            $beforeSend();
        }

        $notificationData = $this->buildNotificationData();

        if ($this->useBulk && $recipients->count() > 10) {
            $this->sendBulkNotifications($recipients, $notificationData);
        } else {
            foreach ($recipients as $recipient) {
                $notification = $this->useQueue
                    ? new QueuedDatabaseNotification($notificationData)
                    : new SynchronousDatabaseNotification($notificationData);

                if ($this->useQueue && $this->queueName !== null) {
                    $notification->onQueue($this->queueName);
                }

                $recipient->notify($notification);
            }
        }

        if ($this->sendBrowserPush && config('webpush.enabled', false)) {
            $this->sendBrowserPushNotifications($recipients, $notificationData);
        }

        if ($afterSend) {
            $afterSend();
        }
    }

    protected function sendBulkNotifications(Collection $recipients, array $notificationData): void
    {
        $now = now();
        $notificationClass = $this->useQueue ? QueuedDatabaseNotification::class : SynchronousDatabaseNotification::class;

        $payload = $recipients->map(function ($recipient) use ($notificationData, $now, $notificationClass) {
            return [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $notificationClass,
                'notifiable_type' => get_class($recipient),
                'notifiable_id' => $recipient->id,
                'data' => json_encode($notificationData),
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        foreach (array_chunk($payload, 100) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }
    }

    protected function buildNotification(): Notification
    {
        $icon = $this->icon instanceof BackedEnum
            ? ($this->icon instanceof Heroicon
                ? 'heroicon-o-'.$this->icon->value
                : (string) $this->icon->value)
            : $this->icon;

        $notification = Notification::make()
            ->title($this->title)
            ->body($this->body, $this->bodyData)
            ->icon($icon);

        match ($this->status) {
            NotificationStatus::Success => $notification->success(),
            NotificationStatus::Warning => $notification->warning(),
            NotificationStatus::Danger => $notification->danger(),
            NotificationStatus::Info => $notification->info(),
        };

        if (! empty($this->actions)) {
            $notification->actions($this->actions);
        }

        return $notification;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildNotificationData(): array
    {
        $notification = $this->buildNotification();

        $data = $notification->toArray();
        $data['duration'] = 'persistent';
        $data['format'] = 'filament';
        unset($data['id']);

        if ($this->actionUrl) {
            $data['actions'] ??= [];

            $viewAction = NotificationAction::make('view')
                ->label(__('View'))
                ->url($this->actionUrl)
                ->markAsRead();

            $data['actions'][] = $viewAction->toArray();
        }

        return $data;
    }

    protected function getUsers(): Collection
    {
        if (isset($this->users)) {
            return $this->users;
        }

        $currentFacility = TenantDatabaseService::getCurrentFacility();

        if (! $currentFacility) {
            if ($this->facilityId) {
                Log::warning('Notification attempted without tenant context; skipping send.', [
                    'facility_id' => $this->facilityId,
                    'branch_id' => $this->branchId,
                ]);
            }

            return collect();
        }

        $query = User::withoutGlobalScopes()
            ->when($this->branchId, function ($query) {
                $query->where(function ($q) {
                    $q->where('branch_id', $this->branchId)
                        ->orWhere('is_hq', true);
                });
            })
            ->when(Auth::check(), function ($query) {
                return $query->where('id', '!=', Auth::id());
            });

        return $query->get();
    }

    protected function sendBrowserPushNotifications(Collection $recipients, array $notificationData): void
    {
        if (! config('webpush.enabled', false)) {
            return;
        }

        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        if (empty($publicKey) || empty($privateKey)) {
            Log::warning('Browser push skipped due to missing VAPID keys.');

            return;
        }

        $facilityId = $this->facilityId ?? TenantDatabaseService::getCurrentFacility()?->id;

        foreach ($recipients as $recipient) {
            if (! method_exists($recipient, 'pushSubscriptions')) {
                continue;
            }

            if (! $facilityId) {
                continue;
            }

            $subscriptionQuery = $recipient->pushSubscriptions()
                ->where('facility_id', $facilityId);

            if (! $subscriptionQuery->exists()) {
                continue;
            }

            $notification = new BrowserPushNotification(
                title: $this->title,
                body: $this->body,
                icon: settings('logo') ?: asset('images/logo.png'),
                url: $this->actionUrl ?? url('/'),
                facilityId: $facilityId
            );

            if ($this->useQueue && $this->queueName) {
                $notification->onQueue($this->queueName);
            }

            try {
                $recipient->notify($notification);
            } catch (\Throwable $exception) {
                Log::error('Failed to send browser push notification', [
                    'recipient_id' => $recipient->getKey(),
                    'facility_id' => $facilityId,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private static function sendNotificationWithLayout(
        int $branchId,
        string $title,
        string $body,
        array $bodyData,
        string $status,
        string|\BackedEnum $icon,
        ?string $resourceClass,
        mixed $record
    ): void {
        $actions = self::generateNotificationActions($resourceClass, $record);

        self::make()
            ->title($title)
            ->description($body, $bodyData)
            ->status($status)
            ->icon($icon)
            ->toBranch($branchId)
            ->actions($actions)
            ->url(self::resolveRecordUrl($resourceClass, $record))
            ->send();
    }

    private static function resolveRecordUrl(?string $resourceClass, mixed $record): ?string
    {
        if (! $resourceClass || ! class_exists($resourceClass)) {
            return null;
        }

        try {
            return $resourceClass::getUrl('view', ['record' => $record]);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function generateNotificationActions(?string $resourceClass, mixed $record): array
    {
        $actions = [];

        if (! $resourceClass || ! class_exists($resourceClass)) {
            return $actions;
        }

        try {
            $viewUrl = $resourceClass::getUrl('view', ['record' => $record]);
            $actions[] = \Filament\Actions\Action::make('view')
                ->label(__('View'))
                ->url($viewUrl)
                ->icon('heroicon-o-eye');
        } catch (\Throwable) {
            // ignore if action unsupported
        }

        try {
            $editUrl = $resourceClass::getUrl('edit', ['record' => $record]);
            $actions[] = \Filament\Actions\Action::make('edit')
                ->label(__('Edit'))
                ->url($editUrl)
                ->icon('heroicon-o-pencil');
        } catch (\Throwable) {
            // ignore if action unsupported
        }

        return $actions;
    }

    private static function generateGeneralNotificationBody(string $modelType, string $action, string $titleValue, array $additionalData = []): string
    {
        $avatarHtml = self::generateAvatarHtml(Auth::user());

        $messageTemplate = __('<b>:creator</b> has :action :modelType <b>:modelName</b>', [
            'creator' => self::getCreatorName(),
            'modelType' => __($modelType),
            'modelName' => $titleValue,
            'action' => __($action),
        ]);

        foreach ($additionalData as $key => $value) {
            $messageTemplate = str_replace(":{$key}", $value, $messageTemplate);
        }

        $commentHtml = '';
        if (! empty($additionalData['comment'])) {
            $commentHtml = '<div class="mt-2 text-sm text-gray-600 dark:text-gray-400">'.$additionalData['comment'].'</div>';
        }

        return '<div class="flex items-start gap-2">'.$avatarHtml.'<div><span>'.$messageTemplate.'</span>'.$commentHtml.'</div></div>';
    }

    private static function generateNotificationBody(string $modelType, string $titleValue): string
    {
        $avatarHtml = self::generateAvatarHtml(Auth::user());
        $message = __('<b>:creator</b> has created :modelType <b>:modelName</b>', [
            'creator' => self::getCreatorName(),
            'modelType' => __($modelType),
            'modelName' => $titleValue,
        ]);

        return '<div class="flex items-start gap-2">'.$avatarHtml.'<span>'.$message.'</span></div>';
    }

    private static function getCreatorName(): string
    {
        return Auth::user()?->name ?? __('System');
    }

    /**
     * @return array{branchId:int|null, branchName:string}
     */
    private static function getTaskBranchInfo(mixed $record): array
    {
        $branchId = null;
        $branchName = __('Unknown Branch');

        if ($record->project_id && $record->project) {
            $branchId = $record->project->branch_id;
            $branchName = $record->project->branch->name;
        } elseif ($record->ticket_id && $record->ticket) {
            $branchId = $record->ticket->branch_id;
            $branchName = $record->ticket->branch->name;
        } elseif ($record->opportunity_id && $record->opportunity) {
            $branchId = $record->opportunity->branch_id;
            $branchName = $record->opportunity->branch->name;
        }

        return compact('branchId', 'branchName');
    }

    private static function generateAvatarHtml(mixed $creator): string
    {
        if (! $creator || ! method_exists($creator, 'getFirstMediaUrl')) {
            return self::generateInitialsAvatar('U');
        }

        $avatarUrl = $creator->getFirstMediaUrl('avatar');

        if ($avatarUrl) {
            return '<img src="'.$avatarUrl.'" alt="'.($creator->name ?? 'User').'" class="w-8 h-8 rounded-full flex-shrink-0" />';
        }

        return self::generateInitialsAvatar($creator->name ?? 'U');
    }

    private static function generateInitialsAvatar(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';

        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1).substr(end($words), 0, 1));
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }

        $colors = [
            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
            '#ec4899', '#f43f5e',
        ];

        $bgColor = $colors[array_rand($colors)];

        $svg = '<svg width="32" height="32" xmlns="http://www.w3.org/2000/svg">
            <circle cx="16" cy="16" r="16" fill="'.$bgColor.'"/>
            <text x="16" y="20" font-family="Arial, sans-serif" font-size="12" font-weight="bold"
                  text-anchor="middle" fill="white">'.$initials.'</text>
        </svg>';

        $dataUrl = 'data:image/svg+xml;base64,'.base64_encode($svg);

        return '<img src="'.$dataUrl.'" alt="'.$name.'" class="w-8 h-8 rounded-full flex-shrink-0" />';
    }
}
