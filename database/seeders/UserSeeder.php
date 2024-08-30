<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create a user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user'); // Assign 'user' role

        // Create an admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin'); // Assign 'admin' role

        // Create a super-admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super_admin'); // Assign 'super-admin' role
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 3f55ba82d508d831a57ca7f8fec0449b41e19245
