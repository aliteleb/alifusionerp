# Filament Notifications Implementation Guide

## Overview

Your Ali Fusion ERP platform now has a complete Filament notifications implementation with database notifications enabled. This guide shows you how to use notifications effectively throughout your application.

## What's Already Set Up

✅ **Filament notifications package** (v4.1.3) installed  
✅ **Database notifications table** created  
✅ **Database notifications enabled** in AdminPanelProvider  
✅ **Notification service** created for easy usage  
✅ **Custom notification classes** for Ali Fusion ERP operations  

## Types of Notifications

### 1. Flash Notifications
Temporary notifications that appear immediately after an action:

```php
use App\Services\NotificationService;

// Simple flash notification
NotificationService::flash(
    __('Task Completed'),
    __('Task has been completed successfully'),
    'success'
);
```

### 2. Database Notifications
Persistent notifications stored in the database and shown in the notification panel:

```php
use App\Services\NotificationService;
use App\Models\User;

// Send to specific user
NotificationService::database(
    $user,
    __('New Task Assigned'),
    __('You have been assigned a new task'),
    'info'
);
```

### 3. Broadcast Notifications
Send to multiple users at once:

```php
// Send to all users in facility
NotificationService::broadcastToFacility(
    __('System Maintenance'),
    __('Scheduled maintenance tonight at 2 AM'),
    'warning'
);

// Send to users with specific role
NotificationService::broadcastToRole(
    'manager',
    __('Monthly Report Ready'),
    __('Monthly sales report is now available'),
    'info'
);
```

## Using in Filament Resources

### In Create Pages
```php
use App\Services\NotificationService;
use Filament\Notifications\Notification;

class CreateTask extends CreateRecord
{
    protected function afterCreate(): void
    {
        // Show success notification with actions
        Notification::make()
            ->title(__('Task Created'))
            ->body(__('Task :name has been added', ['name' => $this->record->title]))
            ->success()
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label(__('View Task'))
                    ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                    ->button(),
            ])
            ->send();
    }
}
```

### In Table Actions
```php
use Filament\Actions\Action;
use App\Services\NotificationService;

Action::make('sendNotification')
    ->label(__('Send Notification'))
    ->form([
        TextInput::make('title')->required(),
        Textarea::make('message')->required(),
    ])
    ->action(function (Task $record, array $data): void {
        NotificationService::broadcastToFacility(
            $data['title'],
            $data['message'],
            'info'
        );
    });
```

## Custom Notification Classes

### Opportunity Status Changed
```php
use App\Notifications\OpportunityStatusChangedNotification;

// Notifies opportunity owner and managers
$user->notify(new OpportunityStatusChangedNotification(
    $opportunity, 
    $oldStatus, 
    $newStatus
));
```

### Task Reminders
```php
use App\Notifications\TaskReminderNotification;

// Send task reminder
$user->notify(new TaskReminderNotification($task));
```

### Ticket Created Notification
```php
use App\Notifications\TicketCreatedNotification;

// Notify about new ticket
$user->notify(new TicketCreatedNotification($ticket));
```

## Notification Service Methods

### Basic Methods
- `flash($title, $body, $type)` - Send flash notification
- `database($user, $title, $body, $type, $actions)` - Send database notification
- `broadcastToFacility($title, $body, $type)` - Send to all facility users
- `broadcastToRole($role, $title, $body, $type)` - Send to users with specific role

### Ali Fusion ERP Methods
- `opportunityStatusChanged($opportunity, $oldStatus, $newStatus)` - Notify about status change
- `ticketCreated($ticket)` - Notify about new ticket creation
- `taskReminder($task)` - Send task reminder
- `sendOverdueTaskReminders()` - Send bulk overdue task reminders
- `exportCompleted($user, $exportType, $downloadUrl)` - Notify about completed export
- `importCompleted($user, $importType, $successCount, $failureCount)` - Notify about import completion

## Notification Types and Colors

- `success` - Green (successful operations)
- `error` - Red (errors, failures)
- `warning` - Yellow (warnings, overdue items)
- `info` - Blue (information, updates)

## Icons Available

Use Heroicon icons for notifications:
- `heroicon-o-user-plus` - New user/customer
- `heroicon-o-check-circle` - Success, completion
- `heroicon-o-x-circle` - Error, failure
- `heroicon-o-clock` - Time-related, reminders
- `heroicon-o-exclamation-triangle` - Warning, overdue
- `heroicon-o-bell` - General notifications
- `heroicon-o-document-text` - Reports, documents

## Testing Notifications

Run the test file to verify everything works:

```bash
php agent-tests/test-filament-notifications.php
```

## Best Practices

### 1. Use Translation Functions
Always wrap notification text with `__()` function:
```php
Notification::make()
    ->title(__('Customer Created'))  // ✅ Correct
    ->body(__('Customer :name has been added', ['name' => $customer->name]))
```

### 2. Provide Action Buttons
Include relevant actions in notifications:
```php
->actions([
    \Filament\Actions\Action::make('view')
        ->label(__('View Details'))
        ->url($url)
        ->button(),
])
```

### 3. Use Appropriate Colors
- Green for success operations
- Red for errors and failures
- Yellow for warnings and overdue items
- Blue for informational updates

### 4. Queue Heavy Operations
Use `ShouldQueue` interface for notification classes that might take time:
```php
class CustomerCreatedNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;
    // ...
}
```

### 5. Batch Notifications
For bulk operations, consider batching notifications to avoid overwhelming users.

## Integration Examples

### In Model Observers
```php
class TaskObserver
{
    public function created(Task $task): void
    {
        NotificationService::taskReminder($task);
    }
}
```

### In Jobs
```php
class ProcessTaskImport implements ShouldQueue
{
    public function handle(): void
    {
        // Process import...
        
        NotificationService::importCompleted(
            $this->user,
            'tasks',
            $successCount,
            $failureCount
        );
    }
}
```

### In Commands
```php
class SendTaskReminders extends Command
{
    public function handle(): void
    {
        NotificationService::sendOverdueTaskReminders();
        
        $this->info('Task reminders sent successfully');
    }
}
```

## Troubleshooting

### Notifications Not Appearing
1. Check if database notifications are enabled in panel configuration
2. Verify the notifications table exists
3. Ensure the Livewire notification component is included in your layout

### Translation Issues
1. Run `php artisan sync:translations` after adding new translatable text
2. Check if translation keys exist in language files

### Performance Issues
1. Use `ShouldQueue` for heavy notification operations
2. Consider batching notifications for bulk operations
3. Monitor database notification table size

## Files Created

1. **`app/Services/NotificationService.php`** - Main service for sending notifications
2. **`app/Notifications/OpportunityStatusChangedNotification.php`** - Opportunity status change notifications
3. **`app/Notifications/TaskReminderNotification.php`** - Task reminder notifications
4. **`app/Notifications/TicketCreatedNotification.php`** - Ticket creation notifications
5. **`app/Filament/Resources/Tasks/Actions/ScheduleTaskReminderAction.php`** - Example action
6. **`app/Filament/Resources/Tasks/Pages/CreateTask.php`** - Example page integration
7. **`docs/FILAMENT_NOTIFICATIONS_GUIDE.md`** - Complete documentation

## Next Steps

1. **Test the implementation** using the provided test file
2. **Integrate notifications** into your existing resources
3. **Create custom notifications** for your specific business needs
4. **Set up scheduled notifications** for reminders and reports
5. **Monitor notification usage** and optimize as needed

Your Filament notifications system is now ready to enhance user experience throughout your Ali Fusion ERP application!
