<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
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

    // API Sync
    Route::get('/sync', [SyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/mollie', [SyncController::class, 'syncMollie'])->name('sync.mollie');
    Route::post('/sync/bunq', [SyncController::class, 'syncBunq'])->name('sync.bunq');
    Route::post('/sync/gmail', [SyncController::class, 'syncGmail'])->name('sync.gmail');
    Route::get('/sync/{sync}', [SyncController::class, 'show'])->name('sync.show');
});

require __DIR__.'/auth.php';
