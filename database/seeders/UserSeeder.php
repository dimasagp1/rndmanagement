<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Superadmin
        $super = User::updateOrCreate(
            ['email' => 'superadmin@herbatech.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $super->syncRoles('Superadmin');

        $this->command->info('✅ Superadmin: superadmin@herbatech.com (password: password)');

        // Staff R&D
        $staff = User::updateOrCreate(
            ['email' => 'staff@herbatech.com'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staff->syncRoles('Staff R&D');

        $this->command->info('✅ Staff R&D: staff@herbatech.com (password: password)');

        // Operational Manager
        $manager = User::updateOrCreate(
            ['email' => 'manager@herbatech.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->syncRoles('Operational Manager');

        $this->command->info('✅ Operational Manager: manager@herbatech.com (password: password)');

        // General Manager (Ibu Lisa)
        $gm = User::updateOrCreate(
            ['email' => 'lisa@herbatech.com'],
            [
                'name' => 'Lisa Wijaya',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $gm->syncRoles('General Manager');

        $this->command->info('✅ General Manager: lisa@herbatech.com (password: password)');

        // Additional Staff R&D untuk testing multi-user
        $staff2 = User::updateOrCreate(
            ['email' => 'siti@herbatech.com'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $staff2->syncRoles('Staff R&D');

        $this->command->info('✅ Staff R&D 2: siti@herbatech.com (password: password)');

        $this->command->info('📊 Total users created: ' . User::count());
    }
}
