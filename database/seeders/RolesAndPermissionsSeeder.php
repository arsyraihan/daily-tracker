<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage-rbac',
            'view-dashboard',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles and assign permissions
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::all());

        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $role->givePermissionTo(['view-dashboard', 'manage-users']);

        $role = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $role->givePermissionTo(['view-dashboard', 'manage-users']);

        $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $role->givePermissionTo(['view-dashboard']);

        // Assign roles to initial accounts
        $admin = User::where('email', 'superadmin@gmail.com')->first();
        if ($admin) {
            $admin->syncRoles(['superadmin']);
        }

        $user = User::where('email', 'chilmi@gmail.com')->first();
        if ($user) {
            $user->update(['name' => 'Chilmi']);
            $user->syncRoles(['employee']);
        }
    }
}
