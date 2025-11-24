<?php

namespace Modules\Survey\Filament\Resources\SurveyInvitationResource\Widgets;

use App\Models\SurveyInvitation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SurveyInvitationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalInvitations = SurveyInvitation::count();
        $activeInvitations = SurveyInvitation::active()->count();
        $completedInvitations = SurveyInvitation::where('status', 'completed')->count();
        $expiredInvitations = SurveyInvitation::expired()->count();
        $queuedInvitations = SurveyInvitation::where('status', 'queued')->count();
        $pendingInvitations = SurveyInvitation::where('status', 'pending')->count();
        $sentInvitations = SurveyInvitation::where('status', 'sent')
            ->orWhere('status', 'viewed')
            ->orWhere('status', 'completed')
            ->count();

        // Completion rate calculated based on sent invitations
        $completionRate = $sentInvitations > 0
            ? round(($completedInvitations / $sentInvitations) * 100, 1)
            : 0;

        // Calculate average response time for completed surveys
        $responseTimeData = SurveyInvitation::where('status', 'completed')
            ->whereNotNull('sent_at')
            ->whereNotNull('completed_at')
            ->whereColumn('completed_at', '>', 'sent_at')
            ->select(DB::raw('AVG(EXTRACT(EPOCH FROM (completed_at - sent_at))) as avg_seconds'))
            ->first();

        $avgResponseTimeSeconds = $responseTimeData ? $responseTimeData->avg_seconds : null;

        // Format the response time in the most appropriate unit
        if ($avgResponseTimeSeconds) {
            if ($avgResponseTimeSeconds < 120) {
                // Less than 2 minutes, show in seconds
                $avgResponseTimeFormatted = round($avgResponseTimeSeconds).' '.__('seconds');
            } elseif ($avgResponseTimeSeconds < 7200) {
                // Less than 2 hours, show in minutes
                $avgResponseTimeFormatted = round($avgResponseTimeSeconds / 60).' '.__('minutes');
            } else {
                // 2 hours or more, show in hours
                $avgResponseTimeFormatted = round($avgResponseTimeSeconds / 3600, 1).' '.__('hours');
            }
        } else {
            $avgResponseTimeFormatted = __('N/A');
        }

        return [
            Stat::make(__('Total Invitations'), $totalInvitations)
                ->description(__('All survey invitations'))
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('primary'),

            Stat::make(__('Sent'), $sentInvitations)
                ->description(__('Delivered to customers'))
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('primary'),

            Stat::make(__('Active Invitations'), $activeInvitations)
                ->description(__('Currently accessible'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make(__('Pending'), $pendingInvitations)
                ->description(__('Created but not sent'))
                ->descriptionIcon('heroicon-m-document')
                ->color('secondary'),

            Stat::make(__('Queued'), $queuedInvitations)
                ->description(__('Waiting to be sent'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('Completed'), $completedInvitations)
                ->description(__('Surveys submitted'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make(__('Expired'), $expiredInvitations)
                ->description(__('Invitations expired'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make(__('Avg Response Time'), $avgResponseTimeFormatted)
                ->description(__('Time to complete'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make(__('Completion Rate'), $completionRate.'%')
                ->description(__('Of sent invitations'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 50 ? 'success' : ($completionRate >= 25 ? 'warning' : 'danger')),
        ];
    }
}
