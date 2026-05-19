<?php

use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntakeDisplayController;
use App\Http\Controllers\PendingIntakeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleAlertController;
use App\Http\Controllers\VehicleModelController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// ── Public ──────────────────────────────────────────────────────────────────
Route::get('/health', fn () => new JsonResponse(['status' => 'ok']));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/intake-display', [IntakeDisplayController::class, 'index'])->name('intake-display');

// ── Protected ────────────────────────────────────────────────────────────────
Route::middleware('auth.user')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/kanban',      [DashboardController::class, 'kanban'])->name('kanban');
    Route::get('/departments', [DashboardController::class, 'departments'])->name('departments');
    Route::get('/customers',   [DashboardController::class, 'customers'])->name('customers');

    Route::post('/auth/change-pin', [AuthController::class, 'changePin'])
         ->name('auth.change-pin');

    Route::post('/tickets', [TicketController::class, 'store'])
         ->name('tickets.store');
    Route::patch('/tickets/{ticket}/stage', [TicketController::class, 'updateStage'])
         ->name('tickets.stage');
    Route::patch('/tickets/{ticket}/plan', [TicketController::class, 'updatePlan'])
         ->name('tickets.plan');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])
         ->name('tickets.destroy');

    Route::post('/intake-queue', [PendingIntakeController::class, 'store'])
         ->name('intake-queue.store');
    Route::patch('/intake-queue/{intake}', [PendingIntakeController::class, 'update'])
         ->name('intake-queue.update');
    Route::post('/intake-queue/{intake}/accept', [PendingIntakeController::class, 'accept'])
         ->name('intake-queue.accept');
    Route::delete('/intake-queue/{intake}', [PendingIntakeController::class, 'destroy'])
         ->name('intake-queue.destroy');

    // ── Users (owner only) ───────────────────────────────────────────────
    Route::get('/accounts', [UserController::class, 'index'])
         ->name('accounts.index');
    Route::post('/users', [UserController::class, 'store'])
         ->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])
         ->name('users.update');
    Route::post('/users/{user}/reset-pin', [UserController::class, 'resetPin'])
         ->name('users.reset-pin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
         ->name('users.destroy');

    // ── App Settings (owner only) ────────────────────────────────────────
    Route::get('/control', [AppSettingController::class, 'index'])
         ->name('control.index');
    Route::patch('/settings', [AppSettingController::class, 'update'])
         ->name('settings.update');

    // ── Vehicle Alerts (owner only) ──────────────────────────────────────
    Route::post('/vehicle-alerts/import', [VehicleAlertController::class, 'import'])
         ->name('vehicle-alerts.import');

    // ── Vehicle Models (owner only) ──────────────────────────────────────
    Route::post('/vehicle-models', [VehicleModelController::class, 'store'])
         ->name('vehicle-models.store');
    Route::delete('/vehicle-models/{model}', [VehicleModelController::class, 'destroy'])
         ->name('vehicle-models.destroy');
});