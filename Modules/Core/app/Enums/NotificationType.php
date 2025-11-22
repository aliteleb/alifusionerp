<?php

namespace Modules\Core\Enums;

enum NotificationType: string
{
    case Creation = 'creation';
    case Update = 'update';
    case Announce = 'announce';
    case Fix = 'fix';
    case Delete = 'delete';
    case Approve = 'approve';
    case Reject = 'reject';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Creation => __('Creation'),
            self::Update => __('Update'),
            self::Announce => __('Announce'),
            self::Fix => __('Fix'),
            self::Delete => __('Delete'),
            self::Approve => __('Approve'),
            self::Reject => __('Reject'),
            self::Complete => __('Complete'),
        };
    }
}
