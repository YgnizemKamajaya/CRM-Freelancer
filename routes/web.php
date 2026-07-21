<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ESignatureController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::view('/offline', 'pwa.offline')->name('offline');

Route::get('/login', [AccessController::class, 'show'])->name('login');
Route::post('/login', [AccessController::class, 'unlock']);
Route::post('/logout', [AccessController::class, 'lock'])->name('logout');

Route::middleware('pin')->group(function () {
    Route::get('/dashboard', \App\Http\Controllers\HomeDashboardController::class)->name('dashboard');
    Route::get('/dashboard/crm', DashboardController::class)->name('dashboard.crm');

    Route::resource('clients', ClientController::class);

    Route::resource('deals', DealController::class);
    Route::post('/deals/{deal}/stage', [DealController::class, 'updateStage'])->name('deals.update-stage');
    Route::post('/deals/{deal}/mark-won', [DealController::class, 'markWon'])->name('deals.mark-won');
    Route::post('/deals/{deal}/mark-lost', [DealController::class, 'markLost'])->name('deals.mark-lost');
    Route::post('/deals/{deal}/convert-to-project', [DealController::class, 'convertToProject'])->name('deals.convert-to-project');

    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/mark-completed', [ProjectController::class, 'markCompleted'])->name('projects.mark-completed');

    Route::resource('tasks', TaskController::class)->except(['show']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
    Route::post('/tasks/{task}/reschedule', [TaskController::class, 'reschedule'])->name('tasks.reschedule');
    Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');

    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.mark-sent');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/finance', FinanceController::class)->name('finance.index');
    Route::resource('wallets', WalletController::class)->except(['show']);
    Route::resource('transactions', TransactionController::class)->except(['show']);
    Route::resource('budgets', BudgetController::class)->except(['show']);
    Route::get('/today', \App\Livewire\TodayChecklist::class)->name('today.index');
    Route::view('/focus', 'focus.index')->name('focus.index');
    Route::post('/goals/{goal}/adjust', [GoalController::class, 'adjust'])->name('goals.adjust');
    Route::post('/goals/{goal}/pin', [GoalController::class, 'togglePin'])->name('goals.pin');
    Route::get('/goals/{goal}/banner', [GoalController::class, 'banner'])->name('goals.banner');
    Route::resource('goals', GoalController::class)->except(['show']);
    Route::get('/workspace', [BoardController::class, 'index'])->name('workspace.index');
    Route::post('/workspace', [BoardController::class, 'store'])->name('workspace.store');
    Route::get('/workspace/{board}', \App\Livewire\BoardShow::class)->name('workspace.show');
    Route::put('/workspace/{board}', [BoardController::class, 'update'])->name('workspace.update');
    Route::delete('/workspace/{board}', [BoardController::class, 'destroy'])->name('workspace.destroy');
    Route::get('/journal/{journal}/export', [JournalController::class, 'export'])->name('journal.export');
    Route::resource('journal', JournalController::class);
    Route::get('/vault', [VaultController::class, 'index'])->name('vault.index');
    Route::post('/vault', [VaultController::class, 'store'])->name('vault.store');
    Route::get('/vault/{file}/preview', [VaultController::class, 'preview'])->name('vault.preview');
    Route::get('/vault/{file}/download', [VaultController::class, 'download'])->name('vault.download');
    Route::delete('/vault/{file}', [VaultController::class, 'destroy'])->name('vault.destroy');

    Route::get('/reports', ReportController::class)->name('reports.index');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::post('/e-signature', [ESignatureController::class, 'store'])->name('esignature.store');
    Route::delete('/e-signature', [ESignatureController::class, 'destroy'])->name('esignature.destroy');
});
