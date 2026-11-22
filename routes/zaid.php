<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventTypeController;
use App\Http\Controllers\Admin\AdminLogoutController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Owner\OwnerCalendarController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Users\BookingReviewController;
use App\Http\Controllers\Users\MyBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::post('/admin/logout', AdminLogoutController::class)
    ->middleware('auth')
    ->name('admin.logout');

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

Route::get('/admin/reviews', [AdminReviewController::class, 'index'])
    ->middleware(['auth', 'admin'])
    ->name('admin.reviews.index');

Route::get('/admin/reviews/{review}', [AdminReviewController::class, 'show'])
    ->middleware(['auth', 'admin'])
    ->name('admin.reviews.show');

Route::delete('/admin/reviews/{review}', [AdminReviewController::class, 'destroy'])
    ->middleware(['auth', 'admin'])
    ->name('admin.reviews.destroy');

Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.dashboard');

Route::get('/owner/calendar', [OwnerCalendarController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.calendar.index');

Route::get('/my-bookings', [MyBookingController::class, 'index'])
    ->middleware('auth')
    ->name('my-bookings.index');

Route::post('/my-bookings/{booking}/review', [BookingReviewController::class, 'store'])
    ->middleware('auth')
    ->whereNumber('booking')
    ->name('my-bookings.reviews.store');

Route::patch('/my-bookings/{booking}/review', [BookingReviewController::class, 'update'])
    ->middleware('auth')
    ->whereNumber('booking')
    ->name('my-bookings.reviews.update');

Route::get('/admin/profile', [AdminProfileController::class, 'edit'])
    ->middleware(['auth', 'admin'])
    ->name('admin.profile.edit');

Route::patch('/admin/profile', [AdminProfileController::class, 'update'])
    ->middleware(['auth', 'admin'])
    ->name('admin.profile.update');
