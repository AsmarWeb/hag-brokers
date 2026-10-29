<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class LaratrustSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new \RuntimeException(
                'Account creation is allowed only in the local environment.'
            );
        }

        $structure = config('laratrust_seeder.roles_structure', []);
        $permissionMap = config('laratrust_seeder.permissions_map', []);

        if (empty($structure)) {
            throw new \RuntimeException('No roles configured.');
        }

        // Create roles and permissions without deleting existing records.
        DB::transaction(function () use ($structure, $permissionMap) {
            foreach ($structure as $roleName => $modules) {
                $role = Role::firstOrCreate(
                    ['name' => $roleName],
                    [
                        'display_name' => ucwords(str_replace(['-', '_'], ' ', $roleName)),
                        'description' => 'HAG Brokers role',
                    ]
                );

                foreach ($modules as $module => $actions) {
                    foreach (explode(',', $actions) as $action) {
                        $permissionAction = $permissionMap[trim($action)] ?? null;

                        if (! $permissionAction) {
                            throw new \RuntimeException('Invalid permission configuration.');
                        }

                        $permission = Permission::firstOrCreate(
                            ['name' => $module . '-' . $permissionAction],
                            [
                                'display_name' => ucfirst($permissionAction) . ' ' . ucfirst($module),
                                'description' => ucfirst($permissionAction) . ' ' . ucfirst($module),
                            ]
                        );

                        $role->permissions()->syncWithoutDetaching([$permission->id]);
                    }
                }
            }
        });

        $accounts = [
            'super-admin' => 'super-admin@hagbrokers.net',
            'admin' => 'admin@hagbrokers.net',
            'operations' => 'operations@hagbrokers.net',
            'sales' => 'sales@hagbrokers.net',
            'customer' => 'customer@hagbrokers.net',
        ];

        foreach ($accounts as $roleName => $email) {
            if (User::where('email', $email)->exists()) {
                $this->command->warn("Existing account skipped: {$email}");
                continue;
            }

            $this->command->info("Creating {$roleName}: {$email}");

            $password = $this->command->secret('Enter password (minimum 12 characters)');

            if (! is_string($password)) {
                throw new \RuntimeException('Password input cancelled.');
            }

            $validation = Validator::make(
                ['password' => $password],
                ['password' => ['required', Password::min(12)]]
            );

            if ($validation->fails()) {
                unset($password);
                throw new \RuntimeException(
                    "Invalid password for {$email}. Run the seeder again."
                );
            }

            DB::transaction(function () use ($roleName, $email, $password) {
                $user = User::create([
                    'name' => ucwords(str_replace(['-', '_'], ' ', $roleName)),
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);

                $user->forceFill(['email_verified_at' => now()])->save();

                $role = Role::where('name', $roleName)->firstOrFail();
                $user->addRole($role);
            });

            unset($password);

            $this->command->info("Created: {$email}");
        }
    }
}
