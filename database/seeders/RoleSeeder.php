<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create roles
        Role::create(['name' => 'user']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'super_admin']);

        // Create a permission for managing users
        Permission::create(['name' => 'manage users']);

        // Assign permission to roles
        $adminRole = Role::findByName('admin');
        $superAdminRole = Role::findByName('super_admin');

        $adminRole->givePermissionTo('manage users');
        $superAdminRole->givePermissionTo('manage users');
    }
}
