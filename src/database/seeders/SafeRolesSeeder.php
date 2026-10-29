<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SafeRolesSeeder extends Seeder
{
    public function run(): void
    {
        $structure = config('laratrust_seeder.roles_structure', []);
        $permissionMap = config('laratrust_seeder.permissions_map', []);

        DB::transaction(function () use ($structure, $permissionMap) {
            foreach ($structure as $roleName => $modules) {
                $role = Role::firstOrCreate(
                    ['name' => $roleName],
                    [
                        'display_name' => ucwords(
                            str_replace(['-', '_'], ' ', $roleName)
                        ),
                        'description' => 'HAG Brokers role',
                    ]
                );

                foreach ($modules as $module => $actions) {
                    foreach (explode(',', $actions) as $action) {
                        $action = trim($action);
                        $permissionAction = $permissionMap[$action] ?? null;

                        if (! $permissionAction) {
                            throw new \RuntimeException(
                                "Unknown permission action: {$action}"
                            );
                        }

                        $permissionName = "{$module}-{$permissionAction}";

                        $permission = Permission::firstOrCreate(
                            ['name' => $permissionName],
                            [
                                'display_name' => ucfirst($permissionAction)
                                    . ' ' . ucfirst($module),
                                'description' => ucfirst($permissionAction)
                                    . ' ' . ucfirst($module),
                            ]
                        );

                        $role->permissions()->syncWithoutDetaching([
                            $permission->id,
                        ]);
                    }
                }
            }
        });
    }
}
