<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerVenueController;


use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Middleware\AdminMiddleware;



Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])
    ->name('admin.users.show');
// Route::get('/', function () {
//     return view('welcome');
// });


require __DIR__ . '/yousef.php';

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');


Route::get('/owner/venues', [OwnerVenueController::class, 'index'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.index');



    Route::get('/owner/venues/create', [OwnerVenueController::class, 'create'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.create');

    Route::get('/owner/venues/{venue}', [OwnerVenueController::class, 'show'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.show');

    Route::post('/owner/venues', [OwnerVenueController::class, 'store'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.store');



    Route::get('/owner/venues/{venue}/edit', [OwnerVenueController::class, 'edit'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.edit');

Route::patch('/owner/venues/{venue}', [OwnerVenueController::class, 'update'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.update');



    Route::patch('/owner/venues/{venue}/availability', [OwnerVenueController::class, 'updateAvailability'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.availability.update');



Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
});


Route::get('/admin/venues', [AdminVenueController::class, 'index'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.index');


    Route::get('/admin/venues/{venue}', [AdminVenueController::class, 'show'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.show');


    Route::patch('/admin/venues/{venue}/approve', [AdminVenueController::class, 'approve'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.approve');

Route::patch('/admin/venues/{venue}/reject', [AdminVenueController::class, 'reject'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.reject');


    Route::delete('/owner/venues/{venue}', [OwnerVenueController::class, 'destroy'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.venues.destroy');


    Route::patch('/admin/venues/{venue}/status', [AdminVenueController::class, 'updateStatus'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.status');

    