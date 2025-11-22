<?php

namespace Modules\System\Actions\Facility\Seeding;

use Modules\Core\Entities\Branch;
use Modules\Core\Entities\Department;
use Modules\Core\Entities\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeedUserDataAction
{
    public function execute(): void
    {
        try {
            Log::info('Starting user seeding');

            // Reset cached roles and permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            // Create default users
            Log::info('Creating default users');
            $this->createDefaultUsers();

            // Final cache clear
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            Log::info('Completed user seeding');

        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Error seeding users: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Create default users for the tenant with appropriate roles.
     */
    private function createDefaultUsers(): void
    {
        Log::info('Starting createDefaultUsers method');

        // Use a transaction to ensure all users are created together
        DB::transaction(function () {
            // Create SuperAdmin user
            $adminUser = $this->createOrUpdateUser(
                'admin@example.com',
                'Administrator',
                'password',
                'SuperAdmin'
            );
            $this->assignOrganizationalData($adminUser, isDepartmentHead: true, isHq: true);

            // Create Supervisor user
            $supervisorUser = $this->createOrUpdateUser(
                'supervisor@example.com',
                'Supervisor',
                'password',
                'Supervisor'
            );
            $this->assignOrganizationalData($supervisorUser, isDepartmentHead: false, isHq: true);

            // Create Participant user
            $participantUser = $this->createOrUpdateUser(
                'participant@example.com',
                'Participant',
                'password',
                'Participant'
            );
            $this->assignOrganizationalData($participantUser, isDepartmentHead: false, isHq: false);

            // Create additional random users for better data distribution
            $this->createRandomUsers();
        });

        Log::info('Completed createDefaultUsers method');
    }

    /**
     * Create additional random users for better data distribution
     */
    private function createRandomUsers(): void
    {
        $roles = ['Supervisor', 'Participant'];
        $names = [
            'أحمد محمد علي', 'فاطمة حسن محمود', 'محمد عبدالله كريم', 'سارة أحمد حسين',
            'علي خالد محمد', 'نورا سعد الدين', 'عمر محمود حسن', 'مريم علي عبدالله',
            'John Smith', 'Sarah Johnson', 'Michael Brown', 'Emily Davis',
            'David Wilson', 'Lisa Anderson', 'Robert Taylor', 'Jennifer Martinez',
            'Ahmad Al-Hassan', 'Fatima Al-Zahra', 'Mohammed Al-Rashid', 'Noura Al-Mahmoud',
            'Khalid Al-Sabah', 'Layla Al-Mansour', 'Omar Al-Baghdadi', 'Zainab Al-Kurdi',
        ];

        $emails = [
            'ahmed.mohammed@company.com', 'fatima.hassan@company.com', 'mohammed.abdullah@company.com',
            'sara.ahmed@company.com', 'ali.khalid@company.com', 'nora.saad@company.com',
            'omar.mahmoud@company.com', 'mariam.ali@company.com', 'john.smith@company.com',
            'sarah.johnson@company.com', 'michael.brown@company.com', 'emily.davis@company.com',
            'david.wilson@company.com', 'lisa.anderson@company.com', 'robert.taylor@company.com',
            'jennifer.martinez@company.com', 'ahmad.hassan@company.com', 'fatima.zahra@company.com',
            'mohammed.rashid@company.com', 'noura.mahmoud@company.com', 'khalid.sabah@company.com',
            'layla.mansour@company.com', 'omar.baghdadi@company.com', 'zainab.kurdi@company.com',
        ];

        // Create 20 additional random users
        for ($i = 0; $i < 20; $i++) {
            $name = $names[array_rand($names)];
            $email = $emails[array_rand($emails)];
            $role = $roles[array_rand($roles)];
            $isDepartmentHead = rand(0, 1) === 1;
            $isHq = rand(0, 1) === 1;

            $user = $this->createOrUpdateUser(
                $email,
                $name,
                'password',
                $role
            );
            $this->assignOrganizationalData($user, isDepartmentHead: $isDepartmentHead, isHq: $isHq);
        }

        Log::info('Created 20 additional random users');
    }

    /**
     * Assign organizational data to a user (branches, departments).
     */
    private function assignOrganizationalData(User $user, bool $isDepartmentHead = false, bool $isHq = false): void
    {
        Log::info("Assigning organizational data to user: {$user->name}");

        try {
            // Assign branches (many-to-many)
            $branches = Branch::active()->get();
            if ($branches->isNotEmpty()) {
                // Attach all active branches, mark random one as primary
                $randomBranch = $branches->random();
                $branchData = [];
                foreach ($branches as $branch) {
                    $branchData[$branch->id] = ['is_primary' => $branch->id === $randomBranch->id];
                }
                $user->branches()->sync($branchData);

                // Set the random branch as primary branch_id
                $user->branch_id = $randomBranch->id;
                Log::info("Assigned {$branches->count()} branches to user: {$user->name}");
                Log::info("Set primary branch_id to {$randomBranch->id} ({$randomBranch->name}) for user: {$user->name}");
            }

            // Assign departments (many-to-many)
            $departments = Department::active()->get();
            if ($departments->isNotEmpty()) {
                // Attach all active departments, mark first as primary
                $departmentData = [];
                foreach ($departments as $index => $department) {
                    $departmentData[$department->id] = ['is_primary' => $index === 0];
                }
                $user->departments()->sync($departmentData);
                Log::info("Assigned {$departments->count()} departments to user: {$user->name}");
            }

            // Set department head status
            $user->is_department_head = $isDepartmentHead;

            // Set HQ status
            $user->is_hq = $isHq;
            Log::info('Set HQ status to '.($isHq ? 'true' : 'false')." for user: {$user->name}");

            // Save the user with new assignments
            $user->save();

            Log::info("Successfully assigned organizational data to user: {$user->name}");
        } catch (Exception $e) {
            Log::error("Error assigning organizational data to user {$user->name}: ".$e->getMessage());
            // Don't throw - allow user creation to succeed even if organizational assignment fails
        }
    }

    /**
     * Create or update a user with proper error handling.
     */
    private function createOrUpdateUser(string $email, string $name, string $password, string $roleName): User
    {
        Log::info("Creating or updating user: {$name} ({$email}) with role: {$roleName}");

        try {
            // First try to find the user
            $user = User::where('email', $email)->first();

            if ($user) {
                Log::info("User already exists: {$name} ({$email})");
                // Update the user attributes
                $user->name = $name;
                $user->save();

                // Assign role if not already assigned and if role exists
                if (! $user->hasRole($roleName) && Role::where('name', $roleName)->exists()) {
                    Log::info("Assigning role {$roleName} to existing user: {$name}");
                    $user->assignRole($roleName);
                }

                return $user;
            }

            // If not found, create new user
            Log::info("Creating new user: {$name} ({$email})");
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]);

            Log::info("Created user with ID: {$user->id}");

            // Assign role to the new user if role exists
            if (Role::where('name', $roleName)->exists()) {
                Log::info("Assigning role {$roleName} to new user: {$name}");
                $user->assignRole($roleName);
            }

            return $user;
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if (str_contains($e->getMessage(), 'unique constraint') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                // User was created by another process, fetch it
                $user = User::where('email', $email)->first();

                if ($user) {
                    Log::info("User was created by another process, fetched: {$name} ({$email})");
                    // Update the user attributes
                    $user->name = $name;
                    $user->save();

                    // Assign role if not already assigned and if role exists
                    if (! $user->hasRole($roleName) && Role::where('name', $roleName)->exists()) {
                        Log::info("Assigning role {$roleName} to fetched user: {$name}");
                        $user->assignRole($roleName);
                    }

                    return $user;
                }
            }

            // Log the error and re-throw if it's a different error
            Log::error("Error creating user {$name} ({$email}): ".$e->getMessage());
            throw $e;
        } catch (Exception $e) {
            // Log any other exceptions
            Log::error("Unexpected error creating user {$name} ({$email}): ".$e->getMessage());
            throw $e;
        }
    }
}
