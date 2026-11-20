<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Owner\OwnerBookingController;



Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

       
        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');

       
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');

        // Only admins can delete reviews (owners have no such route).
        Route::delete('/bookings/{booking}/review', [AdminBookingController::class, 'destroyReview'])
            ->name('bookings.review.destroy');
    });




Route::prefix('owner')
    ->name('owner.')
    ->middleware(['auth', 'owner'])
    ->group(function () {

    
        Route::get('/bookings', [OwnerBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [OwnerBookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/confirm', [OwnerBookingController::class, 'confirm'])
            ->name('bookings.confirm');

        Route::patch('/bookings/{booking}/reject', [OwnerBookingController::class, 'reject'])
            ->name('bookings.reject');
    });