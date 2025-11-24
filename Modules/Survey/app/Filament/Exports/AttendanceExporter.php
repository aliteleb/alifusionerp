<?php

namespace Modules\Survey\Filament\Exports;

use App\Actions\Attendance\AnalyzeAttendanceAction;
use App\Models\Attendance;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AttendanceExporter extends Exporter
{
    protected static ?string $model = Attendance::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('employee.full_name')->label('Employee Name'),
            ExportColumn::make('date')->label('Date'),
            ExportColumn::make('shift_name')->label('Shift Name'),
            ExportColumn::make('shift_start_time')
                ->label('Shift In')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['shift_start_time'] ?? '-';
                }),
            ExportColumn::make('check_in')
                ->label('Check In')
                ->formatStateUsing(fn ($record) => $record->check_in ? $record->check_in->format('H:i:s') : '-'),
            ExportColumn::make('late_entry_minutes')
                ->label('Late Entry (mins)')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['late_entry_minutes'] ?? 0;
                }),
            ExportColumn::make('shift_break_start_time')
                ->label('Shift Break In')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['shift_break_start_time'] ?? '-';
                }),
            ExportColumn::make('break_start')
                ->label('Break In')
                ->formatStateUsing(fn ($record) => $record->break_start ? $record->break_start->format('H:i:s') : '-'),
            ExportColumn::make('shift_break_end_time')
                ->label('Shift Break Out')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['shift_break_end_time'] ?? '-';
                }),
            ExportColumn::make('break_end')
                ->label('Break Out')
                ->formatStateUsing(fn ($record) => $record->break_end ? $record->break_end->format('H:i:s') : '-'),
            ExportColumn::make('shift_end_time')
                ->label('Shift Out')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['shift_end_time'] ?? '-';
                }),
            ExportColumn::make('check_out')
                ->label('Check Out')
                ->formatStateUsing(fn ($record) => $record->check_out ? $record->check_out->format('H:i:s') : '-'),
            ExportColumn::make('early_exit_minutes')
                ->label('Early Exit (mins)')
                ->formatStateUsing(function (Attendance $record) {
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['early_exit_minutes'] ?? 0;
                }),
            ExportColumn::make('overtime')
                ->label('Overtime (mins)')
                ->formatStateUsing(function (Attendance $record) {
                    if ($record->approved_at) {
                        return $record->approved_overtime > 0 ? "{$record->approved_overtime}" : 0;
                    }
                    $analysis = (new AnalyzeAttendanceAction)($record);

                    return $analysis['info']['overtime_minutes'] ?? 0;
                }),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(function (Attendance $record) {
                    if ($record->approved_at) {
                        return $record->approved_note ?? null;
                    }
                    $analysis = (new AnalyzeAttendanceAction)($record);
                    if (isset($analysis['holiday_name'])) {
                        return $analysis['status']?->value;
                    }

                    return $analysis['status']?->value ?? 'No Shift';
                }),
            ExportColumn::make('attendance_note.name')->label('Action'),
            ExportColumn::make('approved_at')->label('Approved At'),
            ExportColumn::make('approver.name')->label('Approved By'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your attendance export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' have been exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
