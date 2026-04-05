<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'manage-purchase-order',
            'manage-shipment',
            'manage-sales-order',
            'manage-uploaded-slips',
            'manage-time-allocation',
            'manage-packing-module',
            'manage-inventory',
            'manage-order-dispatch',
            'manage-payment',
            'manage-reports',
            'manage-masters',
            'manage-users',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission], ['guard_name' => 'admin']);
        }

        // create roles and assign existing permissions
        $adminRole = Role::updateOrCreate(['id' => 1], ['name' => 'Admin', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $ownerRole = Role::updateOrCreate(['id' => 3], ['name' => 'Owner', 'guard_name' => 'admin']);
        // For now, owner might have all or specific permissions. 
        // User said "owner has separate panel" but let's give it basic permissions first.
        $ownerRole->givePermissionTo($permissions);

        // Assign Admin role to existing users with role_id 1
        $admins = User::where('role_id', 1)->get();
        foreach ($admins as $admin) {
            $admin->assignRole($adminRole);
        }

        // Assign Owner role to existing users with role_id 3
        $owners = User::where('role_id', 3)->get();
        foreach ($owners as $owner) {
            $owner->assignRole($ownerRole);
        }
    }
}
