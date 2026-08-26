<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\TrialRmController;
use App\Http\Controllers\TrialPmController;
use App\Http\Controllers\ApprovalCenterController;
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
use App\Http\Controllers\PackagingDevelopmentController;
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

    // ── Regulatory Dossier ────────────────────────────────────
    Route::get('/regulatory-dossiers', [RegulatoryDossierController::class, 'index'])
         ->name('regulatory-dossiers.index')
         ->middleware('can:regulatory_dossier.view');
    Route::get('/regulatory-dossiers/create', [RegulatoryDossierController::class, 'create'])
         ->name('regulatory-dossiers.create')
         ->middleware('can:regulatory_dossier.view');
    Route::post('/regulatory-dossiers', [RegulatoryDossierController::class, 'store'])
         ->name('regulatory-dossiers.store')
         ->middleware('can:regulatory_dossier.view');
    Route::get('/regulatory-dossiers/{regulatoryDossier}', [RegulatoryDossierController::class, 'show'])
         ->name('regulatory-dossiers.show')
         ->middleware('can:regulatory_dossier.view');
    Route::get('/regulatory-dossiers/{regulatoryDossier}/edit', [RegulatoryDossierController::class, 'edit'])
         ->name('regulatory-dossiers.edit')
         ->middleware('can:regulatory_dossier.view');
    Route::put('/regulatory-dossiers/{regulatoryDossier}', [RegulatoryDossierController::class, 'update'])
         ->name('regulatory-dossiers.update')
         ->middleware('can:regulatory_dossier.view');
    Route::delete('/regulatory-dossiers/{regulatoryDossier}', [RegulatoryDossierController::class, 'destroy'])
         ->name('regulatory-dossiers.destroy')
         ->middleware('can:regulatory_dossier.view');

    Route::post('/regulatory-dossiers/{regulatoryDossier}/submit', [RegulatoryDossierController::class, 'submit'])
         ->name('regulatory-dossiers.submit')
         ->middleware('can:regulatory_dossier.view');

    // Checklist documents
    Route::post('/regulatory-dossiers/{regulatoryDossier}/documents', [RegulatoryDossierController::class, 'storeDocument'])
         ->name('regulatory-dossiers.documents.store')
         ->middleware('can:regulatory_dossier.view');
    Route::put('/regulatory-dossiers/{regulatoryDossier}/documents/{document}', [RegulatoryDossierController::class, 'updateDocument'])
         ->name('regulatory-dossiers.documents.update')
         ->middleware('can:regulatory_dossier.view');
    Route::post('/regulatory-dossiers/{regulatoryDossier}/documents/{document}/complete', [RegulatoryDossierController::class, 'completeDocument'])
         ->name('regulatory-dossiers.documents.complete')
         ->middleware('can:regulatory_dossier.view');
    Route::delete('/regulatory-dossiers/{regulatoryDossier}/documents/{document}', [RegulatoryDossierController::class, 'destroyDocument'])
         ->name('regulatory-dossiers.documents.destroy')
         ->middleware('can:regulatory_dossier.view');

    // Folders
    Route::post('/regulatory-dossiers/{regulatoryDossier}/folders', [RegulatoryDossierController::class, 'storeFolder'])
         ->name('regulatory-dossiers.folders.store')
         ->middleware('can:regulatory_dossier.view');
    Route::delete('/regulatory-dossiers/{regulatoryDossier}/folders/{folder}', [RegulatoryDossierController::class, 'destroyFolder'])
         ->name('regulatory-dossiers.folders.destroy')
         ->middleware('can:regulatory_dossier.view');

    // Attachments (upload file / upload folder)
    Route::post('/regulatory-dossiers/{regulatoryDossier}/attachments', [RegulatoryDossierController::class, 'storeAttachment'])
         ->name('regulatory-dossiers.attachments.store')
         ->middleware('can:regulatory_dossier.view');
    Route::delete('/regulatory-dossiers/{regulatoryDossier}/attachments/{attachment}', [RegulatoryDossierController::class, 'destroyAttachment'])
         ->name('regulatory-dossiers.attachments.destroy')
         ->middleware('can:regulatory_dossier.view');

    // Query / Deficiency tracking
    Route::post('/regulatory-dossiers/{regulatoryDossier}/queries', [RegulatoryDossierController::class, 'storeQuery'])
         ->name('regulatory-dossiers.queries.store')
         ->middleware('can:regulatory_dossier.view');
    Route::post('/regulatory-dossiers/{regulatoryDossier}/queries/{query}/respond', [RegulatoryDossierController::class, 'respondQuery'])
         ->name('regulatory-dossiers.queries.respond')
         ->middleware('can:regulatory_dossier.view');
    Route::post('/regulatory-dossiers/{regulatoryDossier}/queries/{query}/status', [RegulatoryDossierController::class, 'updateQueryStatus'])
         ->name('regulatory-dossiers.queries.status')
         ->middleware('can:regulatory_dossier.view');

    // ── Technology Transfer ───────────────────────────────────
    Route::get('/technology-transfers', [TechnologyTransferController::class, 'index'])
         ->name('technology-transfers.index')
         ->middleware('can:technology_transfer.view');
    Route::get('/technology-transfers/create', [TechnologyTransferController::class, 'create'])
         ->name('technology-transfers.create')
         ->middleware('can:technology_transfer.view');
    Route::post('/technology-transfers', [TechnologyTransferController::class, 'store'])
         ->name('technology-transfers.store')
         ->middleware('can:technology_transfer.view');
    Route::get('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'show'])
         ->name('technology-transfers.show')
         ->middleware('can:technology_transfer.view');
    Route::get('/technology-transfers/{technologyTransfer}/edit', [TechnologyTransferController::class, 'edit'])
         ->name('technology-transfers.edit')
         ->middleware('can:technology_transfer.view');
    Route::put('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'update'])
         ->name('technology-transfers.update')
         ->middleware('can:technology_transfer.view');
    Route::delete('/technology-transfers/{technologyTransfer}', [TechnologyTransferController::class, 'destroy'])
         ->name('technology-transfers.destroy')
         ->middleware('can:technology_transfer.view');

    // Approval online: OM → GM
    Route::post('/technology-transfers/{technologyTransfer}/submit', [TechnologyTransferController::class, 'submit'])
         ->name('technology-transfers.submit')
         ->middleware('can:technology_transfer.view');
    Route::post('/technology-transfers/{technologyTransfer}/approve-om', [TechnologyTransferController::class, 'approveOm'])
         ->name('technology-transfers.approve-om')
         ->middleware('can:technology_transfer.view');
    Route::post('/technology-transfers/{technologyTransfer}/approve-gm', [TechnologyTransferController::class, 'approveGm'])
         ->name('technology-transfers.approve-gm')
         ->middleware('can:technology_transfer.view');
    Route::post('/technology-transfers/{technologyTransfer}/reject', [TechnologyTransferController::class, 'reject'])
         ->name('technology-transfers.reject')
         ->middleware('can:technology_transfer.view');

    // Checklist
    Route::post('/technology-transfers/{technologyTransfer}/checklists', [TechnologyTransferController::class, 'storeChecklist'])
         ->name('technology-transfers.checklists.store')
         ->middleware('can:technology_transfer.view');
    Route::put('/technology-transfers/{technologyTransfer}/checklists/{checklist}', [TechnologyTransferController::class, 'updateChecklist'])
         ->name('technology-transfers.checklists.update')
         ->middleware('can:technology_transfer.view');
    Route::post('/technology-transfers/{technologyTransfer}/checklists/{checklist}/complete', [TechnologyTransferController::class, 'completeChecklist'])
         ->name('technology-transfers.checklists.complete')
         ->middleware('can:technology_transfer.view');
    Route::delete('/technology-transfers/{technologyTransfer}/checklists/{checklist}', [TechnologyTransferController::class, 'destroyChecklist'])
         ->name('technology-transfers.checklists.destroy')
         ->middleware('can:technology_transfer.view');

    // Attachments (pdf/word)
    Route::post('/technology-transfers/{technologyTransfer}/attachments', [TechnologyTransferController::class, 'storeAttachment'])
         ->name('technology-transfers.attachments.store')
         ->middleware('can:technology_transfer.view');
    Route::delete('/technology-transfers/{technologyTransfer}/attachments/{attachment}', [TechnologyTransferController::class, 'destroyAttachment'])
         ->name('technology-transfers.attachments.destroy')
         ->middleware('can:technology_transfer.view');

    // ── NIE Approved (Monitoring Nomor Izin Edar) ─────────────
    Route::get('/nie-approvals', [NieApprovalController::class, 'index'])
         ->name('nie-approvals.index')
         ->middleware('can:nie_approval.view');
    Route::get('/nie-approvals/create', [NieApprovalController::class, 'create'])
         ->name('nie-approvals.create')
         ->middleware('can:nie_approval.view');
    Route::post('/nie-approvals', [NieApprovalController::class, 'store'])
         ->name('nie-approvals.store')
         ->middleware('can:nie_approval.view');
    Route::get('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'show'])
         ->name('nie-approvals.show')
         ->middleware('can:nie_approval.view');
    Route::get('/nie-approvals/{nieApproval}/edit', [NieApprovalController::class, 'edit'])
         ->name('nie-approvals.edit')
         ->middleware('can:nie_approval.view');
    Route::put('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'update'])
         ->name('nie-approvals.update')
         ->middleware('can:nie_approval.view');
    Route::delete('/nie-approvals/{nieApproval}', [NieApprovalController::class, 'destroy'])
         ->name('nie-approvals.destroy')
         ->middleware('can:nie_approval.view');

    // Approval online: OM → GM
    Route::post('/nie-approvals/{nieApproval}/submit', [NieApprovalController::class, 'submit'])
         ->name('nie-approvals.submit')
         ->middleware('can:nie_approval.view');
    Route::post('/nie-approvals/{nieApproval}/approve-om', [NieApprovalController::class, 'approveOm'])
         ->name('nie-approvals.approve-om')
         ->middleware('can:nie_approval.view');
    Route::post('/nie-approvals/{nieApproval}/approve-gm', [NieApprovalController::class, 'approveGm'])
         ->name('nie-approvals.approve-gm')
         ->middleware('can:nie_approval.view');
    Route::post('/nie-approvals/{nieApproval}/reject', [NieApprovalController::class, 'reject'])
         ->name('nie-approvals.reject')
         ->middleware('can:nie_approval.view');

    // Attachments (pdf/word/jpg)
    Route::post('/nie-approvals/{nieApproval}/attachments', [NieApprovalController::class, 'storeAttachment'])
         ->name('nie-approvals.attachments.store')
         ->middleware('can:nie_approval.view');
    Route::delete('/nie-approvals/{nieApproval}/attachments/{attachment}', [NieApprovalController::class, 'destroyAttachment'])
         ->name('nie-approvals.attachments.destroy')
         ->middleware('can:nie_approval.view');

    // ── Packaging Development ─────────────────────────────────
    Route::get('/packaging-developments', [PackagingDevelopmentController::class, 'index'])
         ->name('packaging-developments.index')
         ->middleware('can:packaging_development.view');
    Route::get('/packaging-developments/create', [PackagingDevelopmentController::class, 'create'])
         ->name('packaging-developments.create')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments', [PackagingDevelopmentController::class, 'store'])
         ->name('packaging-developments.store')
         ->middleware('can:packaging_development.view');
    Route::get('/packaging-developments/{packagingDevelopment}', [PackagingDevelopmentController::class, 'show'])
         ->name('packaging-developments.show')
         ->middleware('can:packaging_development.view');
    Route::get('/packaging-developments/{packagingDevelopment}/edit', [PackagingDevelopmentController::class, 'edit'])
         ->name('packaging-developments.edit')
         ->middleware('can:packaging_development.view');
    Route::put('/packaging-developments/{packagingDevelopment}', [PackagingDevelopmentController::class, 'update'])
         ->name('packaging-developments.update')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}', [PackagingDevelopmentController::class, 'destroy'])
         ->name('packaging-developments.destroy')
         ->middleware('can:packaging_development.view');

    // Flow approval: OM → GM
    Route::post('/packaging-developments/{packagingDevelopment}/submit', [PackagingDevelopmentController::class, 'submit'])
         ->name('packaging-developments.submit')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/approve-om', [PackagingDevelopmentController::class, 'approveOm'])
         ->name('packaging-developments.approve-om')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/approve-gm', [PackagingDevelopmentController::class, 'approveGm'])
         ->name('packaging-developments.approve-gm')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/reject', [PackagingDevelopmentController::class, 'reject'])
         ->name('packaging-developments.reject')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/duplicate', [PackagingDevelopmentController::class, 'duplicate'])
         ->name('packaging-developments.duplicate')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/stage', [PackagingDevelopmentController::class, 'updateStage'])
         ->name('packaging-developments.stage')
         ->middleware('can:packaging_development.view');

    // Child data: specification, primary, secondary
    Route::post('/packaging-developments/{packagingDevelopment}/specifications', [PackagingDevelopmentController::class, 'saveSpecification'])
         ->name('packaging-developments.specifications.save')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/specifications', [PackagingDevelopmentController::class, 'destroySpecification'])
         ->name('packaging-developments.specifications.destroy')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/primary', [PackagingDevelopmentController::class, 'savePrimary'])
         ->name('packaging-developments.primary.save')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/primary', [PackagingDevelopmentController::class, 'destroyPrimary'])
         ->name('packaging-developments.primary.destroy')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/secondary', [PackagingDevelopmentController::class, 'saveSecondary'])
         ->name('packaging-developments.secondary.save')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/secondary', [PackagingDevelopmentController::class, 'destroySecondary'])
         ->name('packaging-developments.secondary.destroy')
         ->middleware('can:packaging_development.view');

    // Child data: material, supplier
    Route::post('/packaging-developments/{packagingDevelopment}/materials', [PackagingDevelopmentController::class, 'storeMaterial'])
         ->name('packaging-developments.materials.store')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/materials/{material}', [PackagingDevelopmentController::class, 'destroyMaterial'])
         ->name('packaging-developments.materials.destroy')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/suppliers', [PackagingDevelopmentController::class, 'storeSupplier'])
         ->name('packaging-developments.suppliers.store')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/suppliers/{supplier}', [PackagingDevelopmentController::class, 'destroySupplier'])
         ->name('packaging-developments.suppliers.destroy')
         ->middleware('can:packaging_development.view');

    // Child data: trial + parameter
    Route::post('/packaging-developments/{packagingDevelopment}/trials', [PackagingDevelopmentController::class, 'storeTrial'])
         ->name('packaging-developments.trials.store')
         ->middleware('can:packaging_development.view');
    Route::put('/packaging-developments/{packagingDevelopment}/trials/{trial}', [PackagingDevelopmentController::class, 'updateTrial'])
         ->name('packaging-developments.trials.update')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/trials/{trial}', [PackagingDevelopmentController::class, 'destroyTrial'])
         ->name('packaging-developments.trials.destroy')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/trials/{trial}/parameters', [PackagingDevelopmentController::class, 'storeTrialParameter'])
         ->name('packaging-developments.trials.parameters.store')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/trials/{trial}/parameters/{parameter}', [PackagingDevelopmentController::class, 'destroyTrialParameter'])
         ->name('packaging-developments.trials.parameters.destroy')
         ->middleware('can:packaging_development.view');

    // Child data: compatibility + parameter
    Route::post('/packaging-developments/{packagingDevelopment}/compatibilities', [PackagingDevelopmentController::class, 'storeCompatibility'])
         ->name('packaging-developments.compatibilities.store')
         ->middleware('can:packaging_development.view');
    Route::put('/packaging-developments/{packagingDevelopment}/compatibilities/{evaluation}', [PackagingDevelopmentController::class, 'updateCompatibility'])
         ->name('packaging-developments.compatibilities.update')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/compatibilities/{evaluation}', [PackagingDevelopmentController::class, 'destroyCompatibility'])
         ->name('packaging-developments.compatibilities.destroy')
         ->middleware('can:packaging_development.view');
    Route::post('/packaging-developments/{packagingDevelopment}/compatibilities/{evaluation}/parameters', [PackagingDevelopmentController::class, 'storeCompatibilityParameter'])
         ->name('packaging-developments.compatibilities.parameters.store')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/compatibilities/{evaluation}/parameters/{parameter}', [PackagingDevelopmentController::class, 'destroyCompatibilityParameter'])
         ->name('packaging-developments.compatibilities.parameters.destroy')
         ->middleware('can:packaging_development.view');

    // Child data: attachment
    Route::post('/packaging-developments/{packagingDevelopment}/attachments', [PackagingDevelopmentController::class, 'storeAttachment'])
         ->name('packaging-developments.attachments.store')
         ->middleware('can:packaging_development.view');
    Route::delete('/packaging-developments/{packagingDevelopment}/attachments/{attachment}', [PackagingDevelopmentController::class, 'destroyAttachment'])
         ->name('packaging-developments.attachments.destroy')
         ->middleware('can:packaging_development.view');

    // ── Commercial Production (CPB) ───────────────────────────
    Route::get('/commercial-productions', [CommercialProductionController::class, 'index'])
         ->name('commercial-productions.index')
         ->middleware('can:commercial_production.view');
    Route::get('/commercial-productions/create', [CommercialProductionController::class, 'create'])
         ->name('commercial-productions.create')
         ->middleware('can:commercial_production.view');
    Route::post('/commercial-productions', [CommercialProductionController::class, 'store'])
         ->name('commercial-productions.store')
         ->middleware('can:commercial_production.view');
    Route::get('/commercial-productions/{commercialProduction}', [CommercialProductionController::class, 'show'])
         ->name('commercial-productions.show')
         ->middleware('can:commercial_production.view');
    Route::get('/commercial-productions/{commercialProduction}/edit', [CommercialProductionController::class, 'edit'])
         ->name('commercial-productions.edit')
         ->middleware('can:commercial_production.view');
    Route::put('/commercial-productions/{commercialProduction}', [CommercialProductionController::class, 'update'])
         ->name('commercial-productions.update')
         ->middleware('can:commercial_production.view');
    Route::delete('/commercial-productions/{commercialProduction}', [CommercialProductionController::class, 'destroy'])
         ->name('commercial-productions.destroy')
         ->middleware('can:commercial_production.view');

    // Approval online: OM → GM
    Route::post('/commercial-productions/{commercialProduction}/submit', [CommercialProductionController::class, 'submit'])
         ->name('commercial-productions.submit')
         ->middleware('can:commercial_production.view');
    Route::post('/commercial-productions/{commercialProduction}/approve-om', [CommercialProductionController::class, 'approveOm'])
         ->name('commercial-productions.approve-om')
         ->middleware('can:commercial_production.view');
    Route::post('/commercial-productions/{commercialProduction}/approve-gm', [CommercialProductionController::class, 'approveGm'])
         ->name('commercial-productions.approve-gm')
         ->middleware('can:commercial_production.view');
    Route::post('/commercial-productions/{commercialProduction}/reject', [CommercialProductionController::class, 'reject'])
         ->name('commercial-productions.reject')
         ->middleware('can:commercial_production.view');

    // Material requirement ops
    Route::post('/commercial-productions/{commercialProduction}/materials', [CommercialProductionController::class, 'storeMaterial'])
         ->name('commercial-productions.materials.store')
         ->middleware('can:commercial_production.view');
    Route::put('/commercial-productions/{commercialProduction}/materials/{material}', [CommercialProductionController::class, 'updateMaterial'])
         ->name('commercial-productions.materials.update')
         ->middleware('can:commercial_production.view');
    Route::delete('/commercial-productions/{commercialProduction}/materials/{material}', [CommercialProductionController::class, 'destroyMaterial'])
         ->name('commercial-productions.materials.destroy')
         ->middleware('can:commercial_production.view');

    // Attachments (pdf/word)
    Route::post('/commercial-productions/{commercialProduction}/attachments', [CommercialProductionController::class, 'storeAttachment'])
         ->name('commercial-productions.attachments.store')
         ->middleware('can:commercial_production.view');
    Route::delete('/commercial-productions/{commercialProduction}/attachments/{attachment}', [CommercialProductionController::class, 'destroyAttachment'])
         ->name('commercial-productions.attachments.destroy')
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

    // ── QbD Modules (QTPP, CQA, CMA, CPP, Risk, Design Space, Control Strategy) ──
    Route::get('/qbd', [QbdController::class, 'dashboard'])->name('qbd.dashboard')->middleware('role:Superadmin|Staff R&D|Staff Packdev|Operational Manager|General Manager');
    Route::middleware('role:Superadmin|Staff R&D|Staff Packdev|Operational Manager|General Manager')->prefix('preformulation-studies/{study}/qbd')->group(function () {
        Route::get('/', [QbdController::class, 'show'])->name('qbd.show');

        Route::post('qtpp', [QbdController::class, 'saveQtpp'])->name('qbd.qtpp.save');
        Route::post('qtpp-attributes', [QbdController::class, 'storeQtppAttribute'])->name('qbd.qtpp-attributes.store');
        Route::delete('qtpp-attributes/{attribute}', [QbdController::class, 'destroyQtppAttribute'])->name('qbd.qtpp-attributes.destroy');

        Route::post('cqa', [QbdController::class, 'storeCqa'])->name('qbd.cqa.store');
        Route::delete('cqa/{cqa}', [QbdController::class, 'destroyCqa'])->name('qbd.cqa.destroy');

        Route::post('cma', [QbdController::class, 'storeCma'])->name('qbd.cma.store');
        Route::delete('cma/{cma}', [QbdController::class, 'destroyCma'])->name('qbd.cma.destroy');

        Route::post('cpp', [QbdController::class, 'storeCpp'])->name('qbd.cpp.store');
        Route::delete('cpp/{cpp}', [QbdController::class, 'destroyCpp'])->name('qbd.cpp.destroy');

        Route::post('risk', [QbdController::class, 'storeRisk'])->name('qbd.risk.store');
        Route::delete('risk/{risk}', [QbdController::class, 'destroyRisk'])->name('qbd.risk.destroy');

        Route::post('design-space', [QbdController::class, 'storeDesignSpace'])->name('qbd.design-space.store');
        Route::delete('design-space/{designSpace}', [QbdController::class, 'destroyDesignSpace'])->name('qbd.design-space.destroy');

        Route::post('control-strategy', [QbdController::class, 'storeControlStrategy'])->name('qbd.control-strategy.store');
        Route::delete('control-strategy/{controlStrategy}', [QbdController::class, 'destroyControlStrategy'])->name('qbd.control-strategy.destroy');
    });
});

require __DIR__.'/auth.php';
