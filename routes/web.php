<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\TrialRmController;
use App\Http\Controllers\TrialPmController;
use App\Http\Controllers\ApprovalCenterController;
use App\Http\Controllers\ApprovalFormulaDesignController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PreformulationStudyController;
use App\Http\Controllers\QbdController;
use App\Http\Controllers\LogbookPmController;
use App\Http\Controllers\FormulaApprovalController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PrfController;
use App\Http\Controllers\NpdProposalController;
use App\Http\Controllers\SampleEvaluationController;
use App\Http\Controllers\StabilityTestController;
use App\Http\Controllers\RegulatoryDossierController;
use App\Http\Controllers\TechnologyTransferController;
use App\Http\Controllers\NieApprovalController;
use App\Http\Controllers\CommercialProductionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('timeline.index'));

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard - legacy redirect to new Timeline dashboard
    Route::get('/dashboard', fn() => redirect()->route('timeline.index', [], 301))->name('dashboard');

    // Timeline View - Dashboard Baru (landing page setelah login)
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');

    // General NPD Workflow (Coming Soon)
    Route::get('/general', [GeneralController::class, 'index'])->name('general.index');
    Route::get('/general/{tab}', [GeneralController::class, 'show'])
         ->name('general.show')
         ->whereIn('tab', array_keys(GeneralController::TABS));

    // ── Stability Test ──────────────────────────────────────
    Route::get('/stability-tests', [StabilityTestController::class, 'index'])
         ->name('stability-tests.index')
         ->middleware('can:stability_test.view');
    Route::get('/stability-tests/create', [StabilityTestController::class, 'create'])
         ->name('stability-tests.create')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests', [StabilityTestController::class, 'store'])
         ->name('stability-tests.store')
         ->middleware('can:stability_test.view');
    Route::get('/stability-tests/{stabilityTest}', [StabilityTestController::class, 'show'])
         ->name('stability-tests.show')
         ->middleware('can:stability_test.view');
    Route::get('/stability-tests/{stabilityTest}/edit', [StabilityTestController::class, 'edit'])
         ->name('stability-tests.edit')
         ->middleware('can:stability_test.view');
    Route::put('/stability-tests/{stabilityTest}', [StabilityTestController::class, 'update'])
         ->name('stability-tests.update')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}', [StabilityTestController::class, 'destroy'])
         ->name('stability-tests.destroy')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}/attachments/{attachment}', [StabilityTestController::class, 'destroyAttachment'])
         ->name('stability-tests.attachments.destroy')
         ->middleware('can:stability_test.view');
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Formulasi RM ──────────────────────────────────────
    Route::resource('formulas', FormulaController::class)->middleware('can:formula.view');
    Route::get('formulas/{formula}/print', [FormulaController::class, 'print'])
         ->name('formulas.print')
         ->middleware('can:formula.view');
    Route::post('formulas/{formula}/submit',     [FormulaController::class, 'submit'])
         ->name('formulas.submit')
         ->middleware('can:formula.view');
    Route::post('formulas/{formula}/reformulate',[FormulaController::class, 'reformulate'])
         ->name('formulas.reformulate')
         ->middleware('can:formula.view');
    Route::post('formulas/{formula}/complete', [FormulaController::class, 'complete'])
         ->name('formulas.complete')
         ->middleware('can:formula.view');

    // ── PRF (Product Request Form) ──────────────────────────
    Route::resource('prfs', PrfController::class)->middleware('can:prf.view');
    Route::delete('prfs/documents/{document}', [PrfController::class, 'destroyDocument'])
         ->name('prfs.documents.destroy')
         ->middleware('can:prf.view');

    // ── NPD Proposal ─────────────────────────────────────
    Route::resource('npd-proposals', NpdProposalController::class)->middleware('can:npd_proposal.view');
    Route::post('npd-proposals/{npdProposal}/project-status', [NpdProposalController::class, 'updateProjectStatus'])
         ->name('npd-proposals.project-status')
         ->middleware('can:npd_proposal.view');
    Route::delete('npd-proposals/documents/{document}', [NpdProposalController::class, 'destroyDocument'])
         ->name('npd-proposals.documents.destroy')
         ->middleware('can:npd_proposal.view');

    // ── Trial RM ──────────────────────────────────────────
    Route::resource('trial-rms', TrialRmController::class)
         ->middleware('can:trial_rm.view')
         ->parameters(['trial-rms' => 'trialRm']);
    Route::get('trial-rms/{trialRm}/print', [TrialRmController::class, 'print'])
         ->name('trial-rms.print')
         ->middleware('can:trial_rm.view');
    Route::post('trial-rms/{trialRm}/submit', [TrialRmController::class, 'submit'])
         ->name('trial-rms.submit')
         ->middleware('can:trial_rm.view');

    // ── Trial PM ──────────────────────────────────────────
    Route::resource('trial-pms', TrialPmController::class)
         ->middleware('can:trial_pm.view')
         ->parameters(['trial-pms' => 'trialPm']);
    Route::post('trial-pms/{trialPm}/submit',  [TrialPmController::class, 'submit'])
         ->name('trial-pms.submit')
         ->middleware('can:trial_pm.view');
    Route::post('trial-pms/{trialPm}/approve', [TrialPmController::class, 'approve'])
         ->name('trial-pms.approve')
         ->middleware('can:trial_pm.view');
    Route::get('trial-pms/{trialPm}/print', [TrialPmController::class, 'print'])
         ->name('trial-pms.print')
         ->middleware('can:trial_pm.view');

    // ── Sample Evaluation ─────────────────────────────────
    Route::resource('sample-evaluations', SampleEvaluationController::class)
         ->middleware('can:sample_evaluation.view')
         ->parameters(['sample-evaluations' => 'sampleEvaluation']);
    Route::post('sample-evaluations/{sampleEvaluation}/sessions', [SampleEvaluationController::class, 'storeSession'])
         ->name('sample-evaluations.sessions.store')
         ->middleware('can:sample_evaluation.view');
    Route::delete('sample-evaluations/{sampleEvaluation}/sessions/{session}', [SampleEvaluationController::class, 'destroySession'])
         ->name('sample-evaluations.sessions.destroy')
         ->middleware('can:sample_evaluation.view');
    Route::post('sample-evaluations/{sampleEvaluation}/sessions/{session}/attachments', [SampleEvaluationController::class, 'storeAttachment'])
         ->name('sample-evaluations.sessions.attachments.store')
         ->middleware('can:sample_evaluation.view');
    Route::delete('sample-evaluations/{sampleEvaluation}/attachments/{attachment}', [SampleEvaluationController::class, 'destroyAttachment'])
         ->name('sample-evaluations.attachments.destroy')
         ->middleware('can:sample_evaluation.view');

    // ── Log Book PM ───────────────────────────────────────
    Route::get('logbook-pm/print-all', [LogbookPmController::class, 'printAll'])
         ->name('logbook-pm.print-all')
         ->middleware('can:trial_pm.view');
    Route::get('logbook-pm/get-trial-data/{trialPm}', [LogbookPmController::class, 'getTrialData'])
         ->name('logbook-pm.get-trial-data')
         ->middleware('can:trial_pm.view');
    Route::post('logbook-pm/{logbookPm}/approve', [LogbookPmController::class, 'approve'])
         ->name('logbook-pm.approve')
         ->middleware('can:trial_pm.view');
    Route::resource('logbook-pm', LogbookPmController::class)
         ->parameters(['logbook-pm' => 'logbookPm'])
         ->middleware('can:trial_pm.view');

    // ── Formula Approval ──────────────────────────────────
    Route::get('/formula-approvals', [FormulaApprovalController::class, 'index'])
         ->name('formula-approvals.index')
         ->middleware('can:formula.view');
    Route::get('/formula-approvals/create', [FormulaApprovalController::class, 'create'])
         ->name('formula-approvals.create')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals', [FormulaApprovalController::class, 'store'])
         ->name('formula-approvals.store')
         ->middleware('can:formula.view');
    Route::get('/formula-approvals/{formApproval}', [FormulaApprovalController::class, 'show'])
         ->name('formula-approvals.show')
         ->middleware('can:formula.view');
    Route::get('/formula-approvals/{formApproval}/edit', [FormulaApprovalController::class, 'edit'])
         ->name('formula-approvals.edit')
         ->middleware('can:formula.view');
    Route::put('/formula-approvals/{formApproval}', [FormulaApprovalController::class, 'update'])
         ->name('formula-approvals.update')
         ->middleware('can:formula.view');
    Route::delete('/formula-approvals/{formApproval}', [FormulaApprovalController::class, 'destroy'])
         ->name('formula-approvals.destroy')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/approve-om', [FormulaApprovalController::class, 'approveOm'])
         ->name('formula-approvals.approve-om')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/approve-gm', [FormulaApprovalController::class, 'approveGm'])
         ->name('formula-approvals.approve-gm')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/reject', [FormulaApprovalController::class, 'reject'])
         ->name('formula-approvals.reject')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/submit', [FormulaApprovalController::class, 'submit'])
         ->name('formula-approvals.submit')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/duplicate', [FormulaApprovalController::class, 'duplicate'])
         ->name('formula-approvals.duplicate')
         ->middleware('can:formula.view');
    Route::post('/formula-approvals/{formApproval}/attachments', [FormulaApprovalController::class, 'storeAttachment'])
         ->name('formula-approvals.attachments.store')
         ->middleware('can:formula.view');
    Route::delete('/formula-approvals/{formApproval}/attachments/{attachment}', [FormulaApprovalController::class, 'destroyAttachment'])
         ->name('formula-approvals.attachments.destroy')
         ->middleware('can:formula.view');
    Route::patch('/formula-approvals/{formApproval}/tracker', [FormulaApprovalController::class, 'updateTracker'])
         ->name('formula-approvals.tracker.update')
         ->middleware('can:formula.view');

    // ── Approval Formula & Design (terpisah dari Formula Approval) ──
    Route::get('/approval-formula-designs', [ApprovalFormulaDesignController::class, 'index'])
         ->name('approval-formula-designs.index')
         ->middleware('can:formula.view');
    Route::get('/approval-formula-designs/create', [ApprovalFormulaDesignController::class, 'create'])
         ->name('approval-formula-designs.create')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs', [ApprovalFormulaDesignController::class, 'store'])
         ->name('approval-formula-designs.store')
         ->middleware('can:formula.view');
    Route::get('/approval-formula-designs/{formApproval}', [ApprovalFormulaDesignController::class, 'show'])
         ->name('approval-formula-designs.show')
         ->middleware('can:formula.view');
    Route::get('/approval-formula-designs/{formApproval}/edit', [ApprovalFormulaDesignController::class, 'edit'])
         ->name('approval-formula-designs.edit')
         ->middleware('can:formula.view');
    Route::put('/approval-formula-designs/{formApproval}', [ApprovalFormulaDesignController::class, 'update'])
         ->name('approval-formula-designs.update')
         ->middleware('can:formula.view');
    Route::delete('/approval-formula-designs/{formApproval}', [ApprovalFormulaDesignController::class, 'destroy'])
         ->name('approval-formula-designs.destroy')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs/{formApproval}/approve-gm', [ApprovalFormulaDesignController::class, 'approveGm'])
         ->name('approval-formula-designs.approve-gm')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs/{formApproval}/reject', [ApprovalFormulaDesignController::class, 'reject'])
         ->name('approval-formula-designs.reject')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs/{formApproval}/submit', [ApprovalFormulaDesignController::class, 'submit'])
         ->name('approval-formula-designs.submit')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs/{formApproval}/duplicate', [ApprovalFormulaDesignController::class, 'duplicate'])
         ->name('approval-formula-designs.duplicate')
         ->middleware('can:formula.view');
    Route::post('/approval-formula-designs/{formApproval}/attachments', [ApprovalFormulaDesignController::class, 'storeAttachment'])
         ->name('approval-formula-designs.attachments.store')
         ->middleware('can:formula.view');
    Route::delete('/approval-formula-designs/{formApproval}/attachments/{attachment}', [ApprovalFormulaDesignController::class, 'destroyAttachment'])
         ->name('approval-formula-designs.attachments.destroy')
         ->middleware('can:formula.view');
    Route::patch('/approval-formula-designs/{formApproval}/tracker', [ApprovalFormulaDesignController::class, 'updateTracker'])
         ->name('approval-formula-designs.tracker.update')
         ->middleware('can:formula.view');

    // ── Regulatory Dossier — Document & Folder Management ─────────
    Route::get('/regulatory-dossier', [RegulatoryDossierController::class, 'index'])
         ->name('regulatory-dossier.index')
         ->middleware('can:regulatory_dossier.view');
    Route::post('/regulatory-dossier/folders', [RegulatoryDossierController::class, 'storeFolder'])
         ->name('regulatory-dossier.folders.store')
         ->middleware('can:regulatory_dossier.create');
    Route::put('/regulatory-dossier/folders/{folder}', [RegulatoryDossierController::class, 'updateFolder'])
         ->name('regulatory-dossier.folders.update')
         ->middleware('can:regulatory_dossier.edit');
    Route::delete('/regulatory-dossier/folders/{folder}', [RegulatoryDossierController::class, 'destroyFolder'])
         ->name('regulatory-dossier.folders.destroy')
         ->middleware('can:regulatory_dossier.delete');
    Route::post('/regulatory-dossier/documents', [RegulatoryDossierController::class, 'storeDocument'])
         ->name('regulatory-dossier.documents.store')
         ->middleware('can:regulatory_dossier.create');
    Route::get('/regulatory-dossier/documents/{document}', [RegulatoryDossierController::class, 'showDocument'])
         ->name('regulatory-dossier.documents.show')
         ->middleware('can:regulatory_dossier.view');
    Route::put('/regulatory-dossier/documents/{document}', [RegulatoryDossierController::class, 'updateDocument'])
         ->name('regulatory-dossier.documents.update')
         ->middleware('can:regulatory_dossier.edit');
    Route::post('/regulatory-dossier/documents/{document}/move', [RegulatoryDossierController::class, 'moveDocument'])
         ->name('regulatory-dossier.documents.move')
         ->middleware('can:regulatory_dossier.edit');
    Route::delete('/regulatory-dossier/documents/{document}', [RegulatoryDossierController::class, 'destroyDocument'])
         ->name('regulatory-dossier.documents.destroy')
         ->middleware('can:regulatory_dossier.delete');
    Route::get('/regulatory-dossier/documents/{document}/download', [RegulatoryDossierController::class, 'downloadDocument'])
         ->name('regulatory-dossier.documents.download')
         ->middleware('can:regulatory_dossier.view');
    Route::get('/regulatory-dossier/documents/{document}/preview', [RegulatoryDossierController::class, 'previewDocument'])
         ->name('regulatory-dossier.documents.preview')
         ->middleware('can:regulatory_dossier.view');
    Route::get('/regulatory-dossier/versions/{version}/download', [RegulatoryDossierController::class, 'downloadVersion'])
         ->name('regulatory-dossier.versions.download')
         ->middleware('can:regulatory_dossier.view');

    // ── Technology Transfer ───────────────────────────────────
    Route::get('/technology-transfers', [TechnologyTransferController::class, 'index'])
         ->name('technology-transfers.index')
         ->middleware('can:technology_transfer.view');
    Route::get('/technology-transfers/create', [TechnologyTransferController::class, 'create'])
         ->name('technology-transfers.create')
         ->middleware('can:technology_transfer.edit');
    Route::post('/technology-transfers', [TechnologyTransferController::class, 'store'])
         ->name('technology-transfers.store')
         ->middleware('can:technology_transfer.edit');
    Route::get('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'show'])
         ->name('technology-transfers.show')
         ->middleware('can:technology_transfer.view');
    Route::get('/technology-transfers/{technologyTransfer}/edit', [TechnologyTransferController::class, 'edit'])
         ->name('technology-transfers.edit')
         ->middleware('can:technology_transfer.edit');
    Route::put('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'update'])
         ->name('technology-transfers.update')
         ->middleware('can:technology_transfer.edit');
    Route::delete('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'destroy'])
         ->name('technology-transfers.destroy')
         ->middleware('can:technology_transfer.edit');

    // Attachments (pdf/word/img)
    Route::post('/technology-transfers/{technologyTransfer}/attachments', [TechnologyTransferController::class, 'storeAttachment'])
         ->name('technology-transfers.attachments.store')
         ->middleware('can:technology_transfer.edit');
    Route::delete('/technology-transfers/{technologyTransfer}/attachments/{attachment}', [TechnologyTransferController::class, 'destroyAttachment'])
         ->name('technology-transfers.attachments.destroy')
         ->middleware('can:technology_transfer.edit');

    // ── NIE Approved ─────────────────────────────────────────
    Route::get('/nie-approvals', [NieApprovalController::class, 'index'])
         ->name('nie-approvals.index')
         ->middleware('can:nie_approval.view');
    Route::get('/nie-approvals/create', [NieApprovalController::class, 'create'])
         ->name('nie-approvals.create')
         ->middleware('can:nie_approval.edit');
    Route::post('/nie-approvals', [NieApprovalController::class, 'store'])
         ->name('nie-approvals.store')
         ->middleware('can:nie_approval.edit');
    Route::get('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'show'])
         ->name('nie-approvals.show')
         ->middleware('can:nie_approval.view');
    Route::get('/nie-approvals/{nieApproval}/edit', [NieApprovalController::class, 'edit'])
         ->name('nie-approvals.edit')
         ->middleware('can:nie_approval.edit');
    Route::put('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'update'])
         ->name('nie-approvals.update')
         ->middleware('can:nie_approval.edit');
    Route::delete('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'destroy'])
         ->name('nie-approvals.destroy')
         ->middleware('can:nie_approval.edit');

    // Attachments (pdf/word/img)
    Route::post('/nie-approvals/{nieApproval}/attachments', [NieApprovalController::class, 'storeAttachment'])
         ->name('nie-approvals.attachments.store')
         ->middleware('can:nie_approval.edit');
    Route::delete('/nie-approvals/{nieApproval}/attachments/{attachment}', [NieApprovalController::class, 'destroyAttachment'])
         ->name('nie-approvals.attachments.destroy')
         ->middleware('can:nie_approval.edit');

    // ── Commercial Production (Folder/Document Management) ──
    Route::get('/commercial-productions', [CommercialProductionController::class, 'index'])
         ->name('commercial-productions.index')
         ->middleware('can:commercial_production.view');
    Route::post('/commercial-productions/folders', [CommercialProductionController::class, 'storeFolder'])
         ->name('commercial-productions.folders.store')
         ->middleware('can:commercial_production.create');
    Route::put('/commercial-productions/folders/{folder}', [CommercialProductionController::class, 'updateFolder'])
         ->name('commercial-productions.folders.update')
         ->middleware('can:commercial_production.edit');
    Route::delete('/commercial-productions/folders/{folder}', [CommercialProductionController::class, 'destroyFolder'])
         ->name('commercial-productions.folders.destroy')
         ->middleware('can:commercial_production.delete');
    Route::post('/commercial-productions/documents', [CommercialProductionController::class, 'storeDocument'])
         ->name('commercial-productions.documents.store')
         ->middleware('can:commercial_production.create');
    Route::get('/commercial-productions/documents/{document}', [CommercialProductionController::class, 'showDocument'])
         ->name('commercial-productions.documents.show')
         ->middleware('can:commercial_production.view');
    Route::put('/commercial-productions/documents/{document}', [CommercialProductionController::class, 'updateDocument'])
         ->name('commercial-productions.documents.update')
         ->middleware('can:commercial_production.edit');
    Route::post('/commercial-productions/documents/{document}/move', [CommercialProductionController::class, 'moveDocument'])
         ->name('commercial-productions.documents.move')
         ->middleware('can:commercial_production.edit');
    Route::delete('/commercial-productions/documents/{document}', [CommercialProductionController::class, 'destroyDocument'])
         ->name('commercial-productions.documents.destroy')
         ->middleware('can:commercial_production.delete');
    Route::get('/commercial-productions/documents/{document}/download', [CommercialProductionController::class, 'downloadDocument'])
         ->name('commercial-productions.documents.download')
         ->middleware('can:commercial_production.view');
    Route::get('/commercial-productions/documents/{document}/preview', [CommercialProductionController::class, 'previewDocument'])
         ->name('commercial-productions.documents.preview')
         ->middleware('can:commercial_production.view');
    Route::get('/commercial-productions/versions/{version}/download', [CommercialProductionController::class, 'downloadVersion'])
         ->name('commercial-productions.versions.download')
         ->middleware('can:commercial_production.view');

    // ── Approval Center ───────────────────────────────────
    Route::get('/approval-center', [ApprovalCenterController::class, 'index'])
         ->name('approval-center.index')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/formulas/{formula}/approve', [ApprovalCenterController::class, 'approveFormula'])
         ->name('approval-center.formulas.approve')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/formulas/{formula}/reject', [ApprovalCenterController::class, 'rejectFormula'])
         ->name('approval-center.formulas.reject')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/trial-rms/{trialRm}/approve', [ApprovalCenterController::class, 'approveTrialRm'])
         ->name('approval-center.trial-rms.approve')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/trial-rms/{trialRm}/reject', [ApprovalCenterController::class, 'rejectTrialRm'])
         ->name('approval-center.trial-rms.reject')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/trial-pms/{trialPm}/approve', [ApprovalCenterController::class, 'approveTrialPm'])
         ->name('approval-center.trial-pms.approve')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/trial-pms/{trialPm}/reject', [ApprovalCenterController::class, 'rejectTrialPm'])
         ->name('approval-center.trial-pms.reject')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/preformulation-studies/{preformulationStudy}/approve', [ApprovalCenterController::class, 'approvePreformulationStudy'])
         ->name('approval-center.preformulation-studies.approve')
         ->middleware('can:approval_center.access');
    Route::post('/approval-center/preformulation-studies/{preformulationStudy}/reject', [ApprovalCenterController::class, 'rejectPreformulationStudy'])
         ->name('approval-center.preformulation-studies.reject')
         ->middleware('can:approval_center.access');

    // ── User Management (Superadmin Only) ───────────────────
    Route::resource('users', UserController::class)->middleware('role:Superadmin');

    // ── System Settings (Superadmin Only) ───────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('role:Superadmin');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('role:Superadmin');

    // ── Data Master (Superadmin & Staff R&D) ─────────────
    Route::delete('materials/documents/{document}', [MaterialController::class, 'destroyDocument'])->name('materials.documents.destroy')->middleware('role:Superadmin|Staff R&D');
    Route::resource('materials', MaterialController::class)->middleware('role:Superadmin|Staff R&D');
    Route::resource('suppliers', SupplierController::class)->middleware('role:Superadmin|Staff R&D');
    Route::resource('product-categories', ProductCategoryController::class)->middleware('role:Superadmin|Staff R&D');
    Route::resource('products', ProductController::class)->middleware('role:Superadmin|Staff R&D');
Route::resource('preformulation-studies', PreformulationStudyController::class)->middleware('role:Superadmin|Staff R&D|Staff Packdev|Operational Manager|General Manager');
    Route::post('preformulation-studies/{preformulationStudy}/submit', [PreformulationStudyController::class, 'submit'])->name('preformulation-studies.submit')->middleware('role:Superadmin|Staff R&D|Staff Packdev');
    Route::delete('preformulation-studies/documents/{document}', [PreformulationStudyController::class, 'destroyDocument'])->name('preformulation-studies.documents.destroy')->middleware('role:Superadmin|Staff R&D|Staff Packdev');

    // ── QbD (Quality by Design) ─────────────────────────────
    Route::get('/qbds', [QbdController::class, 'index'])
         ->name('qbds.index')
         ->middleware('can:qbd.view');
    Route::get('/qbds/create', [QbdController::class, 'create'])
         ->name('qbds.create')
         ->middleware('can:qbd.edit');
    Route::post('/qbds', [QbdController::class, 'store'])
         ->name('qbds.store')
         ->middleware('can:qbd.edit');
    Route::get('/qbds/{qbd}', [QbdController::class, 'show'])
         ->name('qbds.show')
         ->middleware('can:qbd.view');
    Route::get('/qbds/{qbd}/edit', [QbdController::class, 'edit'])
         ->name('qbds.edit')
         ->middleware('can:qbd.edit');
    Route::put('/qbds/{qbd}', [QbdController::class, 'update'])
         ->name('qbds.update')
         ->middleware('can:qbd.edit');
    Route::delete('/qbds/{qbd}', [QbdController::class, 'destroy'])
         ->name('qbds.destroy')
         ->middleware('can:qbd.edit');

    // Attachments (pdf/word/img)
    Route::post('/qbds/{qbd}/attachments', [QbdController::class, 'storeAttachment'])
         ->name('qbds.attachments.store')
         ->middleware('can:qbd.edit');
    Route::delete('/qbds/{qbd}/attachments/{attachment}', [QbdController::class, 'destroyAttachment'])
         ->name('qbds.attachments.destroy')
         ->middleware('can:qbd.edit');
});

require __DIR__.'/auth.php';
