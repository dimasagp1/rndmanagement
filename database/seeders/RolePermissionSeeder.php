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

        // Create permissions untuk PRF (tanpa approval OM/GM)
        $prfPermissions = [
            'prf.create',
            'prf.view',
            'prf.edit',
            'prf.delete',
        ];

        foreach ($prfPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Hapus permission approval PRF yang tidak lagi digunakan
        Permission::whereIn('name', ['prf.approve_tahap1', 'prf.approve_tahap2'])->delete();

        // Create permissions untuk NPD Proposal (tanpa approval OM/GM)
        $npdProposalPermissions = [
            'npd_proposal.create',
            'npd_proposal.view',
            'npd_proposal.edit',
            'npd_proposal.delete',
        ];

        foreach ($npdProposalPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Hapus permission approval NPD Proposal yang tidak lagi digunakan
        Permission::whereIn('name', ['npd_proposal.approve_tahap1', 'npd_proposal.approve_tahap2'])->delete();

        // Create permissions untuk Sample Evaluation (tanpa approval OM/GM)
        $sampleEvaluationPermissions = [
            'sample_evaluation.create',
            'sample_evaluation.view',
            'sample_evaluation.edit',
            'sample_evaluation.delete',
        ];

        foreach ($sampleEvaluationPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

// Create permissions untuk Packaging Development
        $packagingDevelopmentPermissions = [
            'packaging_development.create',
            'packaging_development.view',
            'packaging_development.edit',
            'packaging_development.delete',
        ];

        foreach ($packagingDevelopmentPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Stability Test (minimal: judul + lampiran)
        $stabilityTestPermissions = [
            'stability_test.create',
            'stability_test.view',
            'stability_test.edit',
            'stability_test.delete',
        ];

        foreach ($stabilityTestPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Technology Transfer
        $technologyTransferPermissions = [
            'technology_transfer.create',
            'technology_transfer.view',
            'technology_transfer.edit',
            'technology_transfer.delete',
        ];

        foreach ($technologyTransferPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk NIE Approved
        $nieApprovalPermissions = [
            'nie_approval.create',
            'nie_approval.view',
            'nie_approval.edit',
            'nie_approval.delete',
        ];

        foreach ($nieApprovalPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk QbD
        $qbdPermissions = [
            'qbd.create',
            'qbd.view',
            'qbd.edit',
            'qbd.delete',
        ];

        foreach ($qbdPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Commercial Production
        $commercialProductionPermissions = [
            'commercial_production.create',
            'commercial_production.view',
            'commercial_production.edit',
            'commercial_production.delete',
        ];

        foreach ($commercialProductionPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create permissions untuk Regulatory Dossier - Document & Folder Management
        $regulatoryDossierPermissions = [
            'regulatory_dossier.create',
            'regulatory_dossier.view',
            'regulatory_dossier.edit',
            'regulatory_dossier.delete',
        ];

        foreach ($regulatoryDossierPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles
        $superadmin = Role::findOrCreate('Superadmin');
        $staffRnd = Role::findOrCreate('Staff R&D');
        $staffPackdev = Role::findOrCreate('Staff Packdev');
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
            'prf.create',
            'prf.view',
            'prf.edit',
            'prf.delete',
            'npd_proposal.create',
            'npd_proposal.view',
            'npd_proposal.edit',
            'npd_proposal.delete',
            'sample_evaluation.create',
            'sample_evaluation.view',
            'sample_evaluation.edit',
            'sample_evaluation.delete',
            'stability_test.create',
            'stability_test.view',
            'stability_test.edit',
            'stability_test.delete',
            'technology_transfer.create',
            'technology_transfer.view',
            'technology_transfer.edit',
            'technology_transfer.delete',
            'nie_approval.create',
            'nie_approval.view',
            'nie_approval.edit',
            'nie_approval.delete',
            'qbd.create',
            'qbd.view',
            'qbd.edit',
            'qbd.delete',
            'commercial_production.create',
            'commercial_production.view',
            'commercial_production.edit',
            'commercial_production.delete',
            'regulatory_dossier.create',
            'regulatory_dossier.view',
            'regulatory_dossier.edit',
            'regulatory_dossier.delete',
            'packaging_development.create',
            'packaging_development.view',
            'packaging_development.edit',
            'packaging_development.delete',
        ]);

        // Assign permissions to Staff Packdev (sama dengan Staff R&D)
        $staffPackdev->syncPermissions([
            'formula.create',
            'formula.view',
            'formula.edit',
            'trial_rm.create',
            'trial_rm.view',
            'trial_rm.edit',
            'trial_pm.create',
            'trial_pm.view',
            'trial_pm.edit',
            'trial_pm.department_approve',
            'prf.create',
            'prf.view',
            'prf.edit',
            'prf.delete',
            'npd_proposal.create',
            'npd_proposal.view',
            'npd_proposal.edit',
            'npd_proposal.delete',
            'sample_evaluation.create',
            'sample_evaluation.view',
            'sample_evaluation.edit',
            'sample_evaluation.delete',
            'stability_test.create',
            'stability_test.view',
            'stability_test.edit',
            'stability_test.delete',
            'technology_transfer.create',
            'technology_transfer.view',
            'technology_transfer.edit',
            'technology_transfer.delete',
            'nie_approval.create',
            'nie_approval.view',
            'nie_approval.edit',
            'nie_approval.delete',
            'commercial_production.create',
            'commercial_production.view',
            'commercial_production.edit',
            'commercial_production.delete',
            'regulatory_dossier.create',
            'regulatory_dossier.view',
            'regulatory_dossier.edit',
            'regulatory_dossier.delete',
            'packaging_development.create',
            'packaging_development.view',
            'packaging_development.edit',
            'packaging_development.delete',
            'qbd.create',
            'qbd.view',
            'qbd.edit',
            'qbd.delete',
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
            'prf.view',
            'npd_proposal.view',
            'sample_evaluation.view',
            'stability_test.view',
            'packaging_development.view',
            'regulatory_dossier.view',
            'technology_transfer.view',
            'nie_approval.create',
            'nie_approval.view',
            'nie_approval.edit',
            'nie_approval.delete',
            'qbd.create',
            'qbd.view',
            'qbd.edit',
            'qbd.delete',
            'commercial_production.view',
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
            'prf.view',
            'npd_proposal.view',
            'sample_evaluation.view',
            'stability_test.view',
            'packaging_development.view',
            'regulatory_dossier.view',
            'technology_transfer.view',
            'nie_approval.create',
            'nie_approval.view',
            'nie_approval.edit',
            'nie_approval.delete',
            'qbd.create',
            'qbd.view',
            'qbd.edit',
            'qbd.delete',
            'commercial_production.view',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully!');
        $this->command->info('📋 4 Roles created/found: Staff R&D, Staff Packdev, Operational Manager, General Manager');
        $this->command->info('🔐 ' . Permission::count() . ' Permissions created/found');
    }
}
