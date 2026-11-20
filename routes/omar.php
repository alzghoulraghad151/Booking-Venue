<?php

use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->name('owner.')->group(function () {
    Route::middleware(['auth', OwnerMiddleware::class])->group(function () {
        Route::get('/venues/{venue}', [OwnerVenueController::class, 'show'])
            ->name('venues.show');

        Route::patch('/venues/{venue}/availability', [OwnerVenueController::class, 'updateAvailability'])
            ->name('venues.availability.update');

        Route::prefix('spaces')->name('spaces.')->group(function () {
            Route::get('/', [OwnerSpaceController::class, 'index'])->name('index');
            Route::get('/create', [OwnerSpaceController::class, 'create'])->name('create');
            Route::post('/', [OwnerSpaceController::class, 'store'])->name('store');
            Route::get('/{space}/edit', [OwnerSpaceController::class, 'edit'])->name('edit');
            Route::put('/{space}', [OwnerSpaceController::class, 'update'])->name('update');
            Route::delete('/{space}', [OwnerSpaceController::class, 'destroy'])->name('destroy');
        });
    });

    Route::get('/space-layouts/{id}/edit', [OwnerSpaceLayoutController::class, 'edit'])
        ->middleware(OwnerMiddleware::class)
        ->name('space-layouts.edit');

    Route::patch('/space-layouts/{id}', [OwnerSpaceLayoutController::class, 'update'])
        ->middleware(['auth', OwnerMiddleware::class])
        ->name('space-layouts.update');
});
