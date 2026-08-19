<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Timeline View (landing page setelah login)
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');

    // General NPD Workflow (Coming Soon)
    Route::get('/general', [GeneralController::class, 'index'])->name('general.index');
    Route::get('/general/{tab}', [GeneralController::class, 'show'])
         ->name('general.show')
         ->whereIn('tab', array_keys(GeneralController::TABS));

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

    // Flow approval: Protokol (OM) → Laporan (GM)
    Route::post('/stability-tests/{stabilityTest}/submit-protocol', [StabilityTestController::class, 'submitProtocol'])
         ->name('stability-tests.submit-protocol')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/approve-protocol', [StabilityTestController::class, 'approveProtocol'])
         ->name('stability-tests.approve-protocol')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/submit-report', [StabilityTestController::class, 'submitReport'])
         ->name('stability-tests.submit-report')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/approve-report', [StabilityTestController::class, 'approveReport'])
         ->name('stability-tests.approve-report')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/reject', [StabilityTestController::class, 'reject'])
         ->name('stability-tests.reject')
         ->middleware('can:stability_test.view');

    // Child data: jadwal, parameter, issue, lampiran
    Route::post('/stability-tests/{stabilityTest}/schedules', [StabilityTestController::class, 'storeSchedule'])
         ->name('stability-tests.schedules.store')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}/schedules/{schedule}', [StabilityTestController::class, 'destroySchedule'])
         ->name('stability-tests.schedules.destroy')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/schedules/{schedule}/parameters', [StabilityTestController::class, 'storeParameter'])
         ->name('stability-tests.parameters.store')
         ->middleware('can:stability_test.view');
    Route::put('/stability-tests/{stabilityTest}/schedules/{schedule}/parameters/{parameter}', [StabilityTestController::class, 'updateParameter'])
         ->name('stability-tests.parameters.update')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}/schedules/{schedule}/parameters/{parameter}', [StabilityTestController::class, 'destroyParameter'])
         ->name('stability-tests.parameters.destroy')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/issues', [StabilityTestController::class, 'storeIssue'])
         ->name('stability-tests.issues.store')
         ->middleware('can:stability_test.view');
    Route::put('/stability-tests/{stabilityTest}/issues/{issue}', [StabilityTestController::class, 'updateIssue'])
         ->name('stability-tests.issues.update')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}/issues/{issue}', [StabilityTestController::class, 'destroyIssue'])
         ->name('stability-tests.issues.destroy')
         ->middleware('can:stability_test.view');
    Route::post('/stability-tests/{stabilityTest}/attachments', [StabilityTestController::class, 'storeAttachment'])
         ->name('stability-tests.attachments.store')
         ->middleware('can:stability_test.view');
    Route::delete('/stability-tests/{stabilityTest}/attachments/{attachment}', [StabilityTestController::class, 'destroyAttachment'])
         ->name('stability-tests.attachments.destroy')
         ->middleware('can:stability_test.view');

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
