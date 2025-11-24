<?php

namespace Modules\Survey\Filament\Widgets;

use App\Models\Branch;
use Filament\Widgets\Widget;

class WhatsAppConfigAlertWidget extends Widget
{
    protected string $view = 'filament.widgets.whatsapp-config-alert-widget';

    protected int|string|array $columnSpan = 'full';

    public function getMissingBranches()
    {
        return Branch::active()
            ->where(function ($query) {
                $query->whereNull('ultramsg_instance_id')
                    ->orWhereNull('ultramsg_token')
                    ->orWhere('ultramsg_enabled', false);
            })
            ->get();
    }

    public static function canView(): bool
    {
        $missingBranches = Branch::active()
            ->where(function ($query) {
                $query->whereNull('ultramsg_instance_id')
                    ->orWhereNull('ultramsg_token')
                    ->orWhere('ultramsg_enabled', false);
            })
            ->exists();

        return $missingBranches;
    }
}
