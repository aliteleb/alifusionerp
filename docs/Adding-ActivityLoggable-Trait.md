# Adding ActivityLoggable Trait to Models

This guide shows how to add the `ActivityLoggable` trait to any model in your Laravel application.

## Quick Setup

### 1. Add the Trait Import

Add the import statement at the top of your model file:

```php
use App\Traits\ActivityLoggable;
```

### 2. Add the Trait to Your Model

Add the trait to your model's `use` statement:

```php
class YourModel extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes; // Add ActivityLoggable here
    
    // Your existing code...
}
```

## Examples for Different Models

### Customer Model

```php
<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'branch_id',
    ];
    
    /**
     * Get fields to exclude from activity logging.
     */
    public function getActivityExcludedFields(): array
    {
        return [
            'credit_card_number',
            'ssn',
            'internal_notes',
        ];
    }
}
```

### Project Model

```php
<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
    ];
    
    /**
     * Get fields to exclude from activity logging.
     */
    public function getActivityExcludedFields(): array
    {
        return [
            'internal_cost',
            'confidential_notes',
        ];
    }
    
    /**
     * Start project and log activity.
     */
    public function start(): void
    {
        $this->update(['status' => 'in_progress', 'start_date' => now()]);
        $this->logStatusChanged('planned', 'in_progress', 'Project started');
    }
    
    /**
     * Complete project and log activity.
     */
    public function complete(): void
    {
        $this->update(['status' => 'completed', 'end_date' => now()]);
        $this->logStatusChanged('in_progress', 'completed', 'Project completed successfully');
    }
}
```

### Ticket Model

```php
<?php

namespace App\Models;

use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use ActivityLoggable, HasFactory, SoftDeletes;
    
    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'assigned_to',
    ];
    
    /**
     * Get fields to exclude from activity logging.
     */
    public function getActivityExcludedFields(): array
    {
        return [
            'internal_notes',
            'system_logs',
        ];
    }
    
    /**
     * Assign ticket and log activity.
     */
    public function assignTo($userId, $userName): void
    {
        $this->update(['assigned_to' => $userId]);
        $this->logAssigned("Ticket assigned to {$userName}");
    }
    
    /**
     * Resolve ticket and log activity.
     */
    public function resolve(): void
    {
        $this->update(['status' => 'resolved']);
        $this->logStatusChanged('open', 'resolved', 'Ticket resolved');
    }
}
```

## Models Already Updated

The following models have already been updated with the `ActivityLoggable` trait:

- ✅ **Branch** - Branch management
- ✅ **Employee** - Employee records
- ✅ **Opportunity** - Sales opportunities
- ✅ **Task** - Task management
- ✅ **User** - User accounts

## Models That Should Be Updated

Consider adding the trait to these models:

- **Customer** - Customer management
- **Project** - Project management
- **Ticket** - Support tickets
- **Contract** - Contract management
- **Deal** - Deal tracking
- **MarketingCampaign** - Marketing campaigns
- **Complaint** - Complaint tracking
- **Announcement** - System announcements

## Testing Your Implementation

After adding the trait to a model, test it:

```php
// Test automatic logging
$model = YourModel::create($data);
$activities = $model->activityLogs()->get();
echo "Activities logged: " . $activities->count();

// Test manual logging
$model->logViewed('Model was viewed');
$model->logExported('Data was exported');

// Test activity summary
$summary = $model->getActivitySummary();
var_dump($summary);
```

## Best Practices

1. **Always define `getActivityExcludedFields()`** for models with sensitive data
2. **Use specific logging methods** when possible (`logViewed()`, `logExported()`, etc.)
3. **Test the implementation** after adding the trait
4. **Consider performance** for high-volume models
5. **Use `withoutActivityLogging()`** for bulk operations

## Performance Tips

- Use `withoutActivityLogging()` for bulk operations
- Consider archiving old activity logs
- Index the `activity_logs` table properly
- Monitor activity log volume in production
