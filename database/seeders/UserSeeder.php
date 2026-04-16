<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@possales.com',
            'password' => Hash::make('admin123'),
        ]);

        // Assign super-admin role to admin user
        $admin->assignRole('super-admin');
    }
}
