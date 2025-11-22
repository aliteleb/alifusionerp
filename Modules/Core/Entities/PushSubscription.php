<?php

namespace Modules\Core\Entities;

use NotificationChannels\WebPush\PushSubscription as BasePushSubscription;

class PushSubscription extends BasePushSubscription
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'facility_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'facility_id' => 'integer',
    ];
}
