<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventTypeController;
use App\Http\Controllers\Owner\OwnerCalendarController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::get('/admin/event-types', [AdminEventTypeController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.index');

Route::get('/admin/event-types/create', [AdminEventTypeController::class, 'create'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.create');

Route::post('/admin/event-types', [AdminEventTypeController::class, 'store'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.store');

Route::get('/admin/event-types/{eventType}/edit', [AdminEventTypeController::class, 'edit'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.edit');

Route::put('/admin/event-types/{eventType}', [AdminEventTypeController::class, 'update'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.update');

Route::delete('/admin/event-types/{eventType}', [AdminEventTypeController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('admin.event-types.destroy');

Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.dashboard');

Route::get('/owner/calendar', [OwnerCalendarController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.calendar.index');
