<?php

use App\Http\Controllers\Owner\OwnerPackageController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->name('owner.')->middleware(['auth', 'owner'])->group(function () {
    Route::get('/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
        ->name('space-layouts.index');

    Route::get('/space-layouts/create', [OwnerSpaceLayoutController::class, 'create'])
        ->name('space-layouts.create');

    Route::post('/space-layouts', [OwnerSpaceLayoutController::class, 'store'])
        ->name('space-layouts.store');

    Route::get('/space-layouts/{id}/edit', [OwnerSpaceLayoutController::class, 'edit'])
        ->name('space-layouts.edit');

    Route::get('/space-layouts/{id}', [OwnerSpaceLayoutController::class, 'show'])
        ->whereNumber('id')
        ->name('space-layouts.show');

    Route::patch('/space-layouts/{id}', [OwnerSpaceLayoutController::class, 'update'])
        ->name('space-layouts.update');

    Route::delete('/space-layouts/{id}', [OwnerSpaceLayoutController::class, 'destroy'])
        ->name('space-layouts.destroy');

    Route::get('/packages', [OwnerPackageController::class, 'index'])
        ->name('packages.index');

    Route::get('/packages/create', [OwnerPackageController::class, 'create'])
        ->name('packages.create');

    Route::post('/packages', [OwnerPackageController::class, 'store'])
    ->name('packages.store');

    Route::get('/packages/{id}/edit', [OwnerPackageController::class, 'edit'])
    ->name('packages.edit');

    Route::patch('/packages/{id}', [OwnerPackageController::class, 'update'])
    ->name('packages.update');

    Route::patch('/packages/{id}/status', [OwnerPackageController::class, 'updateStatus'])
    ->name('packages.status.update');

    Route::get('/packages/{id}', [OwnerPackageController::class, 'show'])
    ->whereNumber('id')
    ->name('packages.show');

    Route::delete('/packages/{id}', [OwnerPackageController::class, 'destroy'])
     ->whereNumber('id')
    ->name('packages.destroy');
});
