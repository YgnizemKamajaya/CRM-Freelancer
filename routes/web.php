<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::view('/offline', 'pwa.offline')->name('offline');

Route::get('/login', [AccessController::class, 'show'])->name('login');
Route::post('/login', [AccessController::class, 'unlock']);
Route::post('/logout', [AccessController::class, 'lock'])->name('logout');

Route::middleware('pin')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

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

    Route::get('/reports', ReportController::class)->name('reports.index');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
