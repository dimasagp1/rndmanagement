<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            MaterialSupplierSeeder::class,
            DemoDataSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('🌿 ============================================');
        $this->command->info('🌿  Herbatech R&D Management System');
        $this->command->info('🌿  Database Seeding Completed!');
        $this->command->info('🌿 ============================================');
        $this->command->info('');
        $this->command->info('📧 Login Credentials:');
        $this->command->info('   Staff R&D:           staff@herbatech.com');
        $this->command->info('   Operational Manager: manager@herbatech.com');
        $this->command->info('   General Manager:     lisa@herbatech.com');
        $this->command->info('   Password (all):      password');
        $this->command->info('');
    }
}
