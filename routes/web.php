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
use App\Http\Controllers\LogbookPmController;
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

    // ── User Management (Superadmin Only) ───────────────────
    Route::resource('users', UserController::class)->middleware('role:Superadmin');

    // ── System Settings (Superadmin Only) ───────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('role:Superadmin');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('role:Superadmin');

    // ── Data Master (Superadmin & Staff R&D) ─────────────
    Route::resource('materials', MaterialController::class)->middleware('role:Superadmin|Staff R&D');
    Route::resource('suppliers', SupplierController::class)->middleware('role:Superadmin|Staff R&D');
});

require __DIR__.'/auth.php';
