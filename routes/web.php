<?php
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Middleware\AdminMiddleware;



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.space-layouts.index');

Route::get('/owner/space-layouts/create', [OwnerSpaceLayoutController::class, 'create'])
    ->middleware(['auth', 'owner'])
    ->name('owner.space-layouts.create');

Route::post('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'store'])
    ->middleware(['auth', 'owner'])
    ->name('owner.space-layouts.store');


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
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
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



    Route::get('/owner/space-layouts/{id}/edit', [OwnerSpaceLayoutController::class, 'edit'])
    ->middleware(OwnerMiddleware::class)
    ->name('owner.space-layouts.edit');

Route::patch('/owner/space-layouts/{id}', [OwnerSpaceLayoutController::class, 'update'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.space-layouts.update');

require __DIR__ . '/zaid.php';

    Route::patch('/admin/venues/{venue}/status', [AdminVenueController::class, 'updateStatus'])
    ->middleware(['auth', AdminMiddleware::class])
    ->name('admin.venues.status');

    
