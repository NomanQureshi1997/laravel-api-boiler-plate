<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or update the admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Get all permissions
        $allPermissions = Permission::all();

        // Sync all permissions to the admin role
        $adminRole->syncPermissions($allPermissions);

        $this->command->info('Admin role synced with all permissions.');
    }
}

