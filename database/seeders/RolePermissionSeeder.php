<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions untuk Formulasi RM
        $formulaPermissions = [
            'formula.create',
            'formula.view',
            'formula.edit',
            'formula.delete',
            'formula.approve_tahap1',  // Operational Manager
            'formula.approve_tahap2',  // General Manager
        ];

        foreach ($formulaPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Trial RM
        $trialRmPermissions = [
            'trial_rm.create',
            'trial_rm.view',
            'trial_rm.edit',
            'trial_rm.delete',
            'trial_rm.approve_tahap1',
            'trial_rm.approve_tahap2',
        ];

        foreach ($trialRmPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Trial PM
        $trialPmPermissions = [
            'trial_pm.create',
            'trial_pm.view',
            'trial_pm.edit',
            'trial_pm.delete',
            'trial_pm.approve_tahap1',
            'trial_pm.approve_tahap2',
            'trial_pm.department_approve',  // untuk 4 departemen
        ];

        foreach ($trialPmPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Approval Center
        Permission::findOrCreate('approval_center.access');

        // Create roles
        $staffRnd = Role::findOrCreate('Staff R&D');
        $operationalManager = Role::findOrCreate('Operational Manager');
        $generalManager = Role::findOrCreate('General Manager');

        // Assign permissions to Staff R&D
        $staffRnd->syncPermissions([
            'formula.create',
            'formula.view',
            'formula.edit',
            'trial_rm.create',
            'trial_rm.view',
            'trial_rm.edit',
            'trial_pm.create',
            'trial_pm.view',
            'trial_pm.edit',
            'trial_pm.department_approve', // bisa approve sebagai perwakilan departemen
        ]);

        // Assign permissions to Operational Manager
        $operationalManager->syncPermissions([
            'formula.view',
            'formula.approve_tahap1',
            'trial_rm.view',
            'trial_rm.approve_tahap1',
            'trial_pm.view',
            'trial_pm.approve_tahap1',
            'approval_center.access',
        ]);

        // Assign permissions to General Manager
        $generalManager->syncPermissions([
            'formula.view',
            'formula.approve_tahap2',
            'trial_rm.view',
            'trial_rm.approve_tahap2',
            'trial_pm.view',
            'trial_pm.approve_tahap2',
            'approval_center.access',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully!');
        $this->command->info('📋 3 Roles created/found: Staff R&D, Operational Manager, General Manager');
        $this->command->info('🔐 ' . Permission::count() . ' Permissions created/found');
    }
}
