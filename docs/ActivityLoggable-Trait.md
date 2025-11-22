# ActivityLoggable Trait Documentation

The `ActivityLoggable` trait provides comprehensive activity logging functionality for Laravel models. It automatically logs CRUD operations and provides methods for manual activity logging with full multi-language support.

## Features

- ✅ **Automatic CRUD Logging**: Automatically logs create, update, delete, and restore operations
- ✅ **Manual Activity Logging**: Rich set of methods for logging custom activities
- ✅ **Multi-Language Support**: All activities are logged with proper translations
- ✅ **Sensitive Data Filtering**: Automatically filters out passwords and sensitive fields
- ✅ **Activity Querying**: Easy methods to query and filter activity logs
- ✅ **Disable/Enable Logging**: Control when activity logging should be disabled
- ✅ **Multi-Tenant Support**: Works seamlessly with tenant databases
- ✅ **Activity Summaries**: Get comprehensive activity summaries and counts

## Installation

Add the trait to your model:

```php
<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use ActivityLoggable;
    
    // Your model code...
}
```

## Automatic Logging

The trait automatically logs the following operations:

- **Created**: When a new record is created
- **Updated**: When a record is updated (with change details)
- **Deleted**: When a record is deleted
- **Restored**: When a soft-deleted record is restored

### Example

```php
// This will automatically log a "created" activity
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// This will automatically log an "updated" activity with change details
$user->update(['name' => 'Jane Doe']);

// This will automatically log a "deleted" activity
$user->delete();
```

## Manual Activity Logging

### Basic Logging

```php
// Log a custom activity
$user->logCustomActivity(ActivityAction::VIEWED, 'User profile was viewed');

// Log with properties
$user->logCustomActivity(
    ActivityAction::EXPORTED, 
    'User data was exported',
    ['format' => 'excel', 'rows' => 150]
);
```

### Specific Activity Methods

```php
// Log viewing
$user->logViewed('User profile was accessed');

// Log export
$user->logExported('User data exported to Excel');

// Log import
$user->logImported('User data imported from CSV');

// Log approval
$user->logApproved('User account approved by admin');

// Log rejection
$user->logRejected('User account rejected due to invalid data');

// Log assignment
$user->logAssigned('User assigned to project team');

// Log unassignment
$user->logUnassigned('User removed from project team');

// Log status change
$user->logStatusChanged('Pending', 'Active', 'Account activated');

// Log comment
$user->logCommented('Comment added to user profile');

// Log notification
$user->logNotified('Welcome email sent to user');

// Log system activity
$user->logSystemActivity('User data synchronized with external system');
```

## Activity Querying

### Get All Activities

```php
// Get all activities for this model
$activities = $user->activityLogs()->get();

// Get recent activities (last 7 days)
$recentActivities = $user->recentActivityLogs(7)->get();

// Get activities by action
$viewedActivities = $user->activityLogsByAction(ActivityAction::VIEWED)->get();
```

### Activity Information

```php
// Check if model has activities
if ($user->hasActivityLogs()) {
    // Get last activity
    $lastActivity = $user->lastActivity();
    
    // Get activity count
    $activityCount = $user->activity_count;
    
    // Get activity summary
    $summary = $user->getActivitySummary();
    // Returns: ['created' => ['action' => ..., 'count' => 1, 'label' => 'Created'], ...]
}
```

## Disable/Enable Logging

### Disable Logging

```php
// Create without logging
$user = User::withoutActivityLogging()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Update without logging
$user->withoutActivityLogging()->update(['name' => 'Jane Doe']);

// Re-enable logging
$user->withActivityLogging();
$user->update(['name' => 'John Smith']); // This will be logged
```

### Check Logging Status

```php
if ($user->isActivityLoggingDisabled()) {
    echo "Activity logging is disabled";
} else {
    echo "Activity logging is enabled";
}
```

## Sensitive Data Filtering

The trait automatically filters out sensitive fields from activity logs:

### Default Excluded Fields

- `updated_at`, `created_at`, `deleted_at`
- `remember_token`, `email_verified_at`
- `password`, `password_confirmation`
- `api_token`, `last_login_at`, `last_activity_at`

### Custom Excluded Fields

Add a method to your model to exclude additional fields:

```php
class User extends Model
{
    use ActivityLoggable;
    
    /**
     * Get fields to exclude from activity logging.
     */
    public function getActivityExcludedFields(): array
    {
        return [
            'secret_key',
            'private_notes',
            'internal_id',
        ];
    }
}
```

## Multi-Language Support

All activities are automatically logged with proper translations:

### English
- "User was created"
- "User was updated"
- "User was deleted"

### Arabic
- "تم إنشاء المستخدم"
- "تم تحديث المستخدم"
- "تم حذف المستخدم"

### Kurdish
- "بەکارهێنەر دروستکرا"
- "بەکارهێنەر نوێکرایەوە"
- "بەکارهێنەر سڕایەوە"

## ActivityAction Enum

The trait uses the `ActivityAction` enum for type-safe activity logging:

```php
use App\Enums\ActivityAction;

// Available actions
ActivityAction::CREATED
ActivityAction::UPDATED
ActivityAction::DELETED
ActivityAction::RESTORED
ActivityAction::LOGIN
ActivityAction::LOGOUT
ActivityAction::LOGIN_FAILED
ActivityAction::PASSWORD_CHANGED
ActivityAction::PROFILE_UPDATED
ActivityAction::EXPORTED
ActivityAction::IMPORTED
ActivityAction::VIEWED
ActivityAction::DOWNLOADED
ActivityAction::UPLOADED
ActivityAction::APPROVED
ActivityAction::REJECTED
ActivityAction::ASSIGNED
ActivityAction::UNASSIGNED
ActivityAction::STATUS_CHANGED
ActivityAction::COMMENTED
ActivityAction::NOTIFIED
ActivityAction::SYSTEM
```

## Multi-Tenant Support

The trait works seamlessly with multi-tenant databases:

- Automatically detects tenant context
- Logs activities in the correct database
- Handles branch information for tenant models
- Maintains proper user relationships

## Examples

### Complete Example

```php
<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use ActivityLoggable;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
    ];
    
    /**
     * Get fields to exclude from activity logging.
     */
    public function getActivityExcludedFields(): array
    {
        return [
            'internal_notes',
            'credit_card_number',
        ];
    }
    
    /**
     * Approve customer and log activity.
     */
    public function approve(): void
    {
        $this->update(['status' => 'approved']);
        $this->logApproved('Customer account approved by manager');
    }
    
    /**
     * Get customer activity summary.
     */
    public function getActivityReport(): array
    {
        return [
            'total_activities' => $this->activity_count,
            'summary' => $this->getActivitySummary(),
            'recent_activities' => $this->recentActivityLogs(30)->get(),
        ];
    }
}
```

### Usage

```php
// Create customer (automatically logged)
$customer = Customer::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'status' => 'pending',
]);

// Log viewing
$customer->logViewed('Customer profile viewed by sales team');

// Approve customer (logs approval)
$customer->approve();

// Get activity report
$report = $customer->getActivityReport();

// Export without logging
$customer->withoutActivityLogging()->update(['status' => 'exported']);
```

## Best Practices

1. **Use Specific Methods**: Use specific logging methods (`logViewed()`, `logExported()`) instead of generic `logCustomActivity()` when possible.

2. **Exclude Sensitive Fields**: Always define `getActivityExcludedFields()` for models with sensitive data.

3. **Disable Logging for Bulk Operations**: Use `withoutActivityLogging()` for bulk operations to avoid performance issues.

4. **Use Descriptive Descriptions**: Provide clear, descriptive messages for manual activities.

5. **Monitor Activity Volume**: Be aware of activity log volume in high-traffic applications.

## Performance Considerations

- Activity logging adds a small overhead to model operations
- Use `withoutActivityLogging()` for bulk operations
- Consider archiving old activity logs periodically
- Index the `activity_logs` table on `model_type`, `model_id`, and `created_at`

## Troubleshooting

### Common Issues

1. **Missing Translations**: Run `php artisan sync:translations` after adding new activity types.

2. **Database Errors**: Ensure the `activity_logs` table exists and has proper indexes.

3. **Performance Issues**: Use `withoutActivityLogging()` for bulk operations.

4. **Missing Activities**: Check if logging is disabled with `isActivityLoggingDisabled()`.

### Debugging

```php
// Check if trait is loaded
$traits = class_uses(YourModel::class);
var_dump(in_array('App\Traits\ActivityLoggable', $traits));

// Check activity logging status
if ($model->isActivityLoggingDisabled()) {
    echo "Activity logging is disabled";
}

// Check recent activities
$activities = $model->recentActivityLogs(1)->get();
var_dump($activities->toArray());
```
