<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EnsureDossierPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'commercial_production.create',
            'commercial_production.view',
            'commercial_production.edit',
            'commercial_production.delete',
            'regulatory_dossier.create',
            'regulatory_dossier.view',
            'regulatory_dossier.edit',
            'regulatory_dossier.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $staffRoles = ['Staff R&D', 'Staff Packdev', 'Staff', 'Staf'];

        foreach ($staffRoles as $roleName) {
            try {
                $role = Role::findByName($roleName);
                $role->givePermissionTo($permissions);
                $this->command->info("Permissions assigned to role: {$roleName}");
            } catch (\Exception $e) {
                $this->command->warn("Role '{$roleName}' not found, skipping.");
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
