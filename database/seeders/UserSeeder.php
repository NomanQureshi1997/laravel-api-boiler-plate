<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the users you want to create
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'nomanjavaid1997@gamil.com',
                'password' => Hash::make('*2|6r77P5}fA'), // Use a secure password in production
                'role' => 'admin',
            ],
            [
                'name' => 'Manager User',
                'email' => 'kamran.mujahid169@gmail.com',
                'password' => Hash::make('*2|6r77P5}fA'), // Use a secure password in production
                'role' => 'manager',
            ],
        ];

        // Loop through each user and create or update them
        foreach ($users as $userData) {
            // Create or update the role
            $role = Role::firstOrCreate(['name' => $userData['role']]);

            // Create or update the user
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );

            // Assign the role to the user
            $user->assignRole($role);

            $this->command->info("User {$userData['email']} created and assigned the {$userData['role']} role.");
        }
    }
}
