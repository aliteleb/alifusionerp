<?php

namespace Modules\Survey\Filament\Resources\SurveyInvitationResource\Pages;

use App\Enums\InvitationStatusEnum;
use App\Models\SurveyInvitation;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Modules\Survey\Filament\Resources\SurveyInvitationResource;
use Modules\Survey\Filament\Resources\SurveyInvitationResource\Widgets\SurveyInvitationStatsWidget;
use Modules\Survey\Filament\Widgets\WhatsAppConfigAlertWidget;

class ListSurveyInvitations extends ListRecords
{
    protected static string $resource = SurveyInvitationResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All'))
                ->modifyQueryUsing(fn (Builder $query) => $query),

            'pending' => Tab::make(__('Pending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::PENDING))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::PENDING)->count()),

            'queued' => Tab::make(__('Queued'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::QUEUED))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::QUEUED)->count()),

            'sent' => Tab::make(__('Sent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::SENT))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::SENT)->count()),

            'viewed' => Tab::make(__('Viewed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::VIEWED))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::VIEWED)->count()),

            'completed' => Tab::make(__('Completed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::COMPLETED))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::COMPLETED)->count()),

            'expired' => Tab::make(__('Expired'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::EXPIRED))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::EXPIRED)->count()),

            'cancelled' => Tab::make(__('Cancelled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', InvitationStatusEnum::CANCELLED))
                ->badge(fn () => SurveyInvitation::where('status', InvitationStatusEnum::CANCELLED)->count()),

            'active' => Tab::make(__('Active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->active())
                ->badge(fn () => SurveyInvitation::active()->count()),

            'ready_to_send' => Tab::make(__('Ready to Send'))
                ->modifyQueryUsing(fn (Builder $query) => $query->readyToSend())
                ->badge(fn () => SurveyInvitation::readyToSend()->count()),

            'scheduled' => Tab::make(__('Scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('send_after'))
                ->badge(fn () => SurveyInvitation::whereNotNull('send_after')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WhatsAppConfigAlertWidget::class,
            SurveyInvitationStatsWidget::class,
        ];
    }
}
