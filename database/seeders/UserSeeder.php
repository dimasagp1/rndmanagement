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
        $super = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@herbatech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $super->assignRole('Superadmin');

        $this->command->info('✅ Superadmin: superadmin@herbatech.com (password: password)');

        // Staff R&D
        $staff = User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'staff@herbatech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $staff->assignRole('Staff R&D');

        $this->command->info('✅ Staff R&D: staff@herbatech.com (password: password)');

        // Operational Manager
        $manager = User::create([
            'name' => 'Budi Santoso',
            'email' => 'manager@herbatech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('Operational Manager');

        $this->command->info('✅ Operational Manager: manager@herbatech.com (password: password)');

        // General Manager (Ibu Lisa)
        $gm = User::create([
            'name' => 'Lisa Wijaya',
            'email' => 'lisa@herbatech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $gm->assignRole('General Manager');

        $this->command->info('✅ General Manager: lisa@herbatech.com (password: password)');

        // Additional Staff R&D untuk testing multi-user
        $staff2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@herbatech.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $staff2->assignRole('Staff R&D');

        $this->command->info('✅ Staff R&D 2: siti@herbatech.com (password: password)');

        $this->command->info('📊 Total users created: ' . User::count());
    }
}
