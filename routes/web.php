<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

// Public homepage - redirect to dashboard if authenticated
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Invoices (Income)
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])
        ->name('invoices.mark-as-paid');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::post('expenses/{expense}/mark-as-paid', [ExpenseController::class, 'markAsPaid'])
        ->name('expenses.mark-as-paid');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Gmail OAuth
    Route::get('/gmail/auth', [SyncController::class, 'gmailAuth'])->name('gmail.auth');
    Route::get('/gmail/callback', [SyncController::class, 'gmailCallback'])->name('gmail.callback');

    // API Sync
    Route::get('/sync', [SyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/herdenkingsportaal', [SyncController::class, 'syncHerdenkingsportaal'])->name('sync.herdenkingsportaal');
    Route::post('/sync/mollie', [SyncController::class, 'syncMollie'])->name('sync.mollie');
    Route::post('/sync/bunq', [SyncController::class, 'syncBunq'])->name('sync.bunq');
    Route::post('/sync/gmail', [SyncController::class, 'syncGmail'])->name('sync.gmail');
    Route::get('/sync/{sync}', [SyncController::class, 'show'])->name('sync.show');

    // Reconciliation (Duplicate Matching)
    Route::get('/reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('/reconciliation/link', [ReconciliationController::class, 'link'])->name('reconciliation.link');
    Route::delete('/reconciliation/{invoice}/unlink', [ReconciliationController::class, 'unlink'])->name('reconciliation.unlink');

    // Reports & Tax Exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/quarterly', [ReportController::class, 'exportQuarterly'])->name('reports.quarterly');
    Route::post('/reports/yearly', [ReportController::class, 'exportYearly'])->name('reports.yearly');
    Route::post('/reports/btw', [ReportController::class, 'exportBTW'])->name('reports.btw');
    Route::get('/reports/download/{filename}', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{filename}', [ReportController::class, 'delete'])->name('reports.delete');
});

require __DIR__.'/auth.php';
