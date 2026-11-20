<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Controllers\Owner\OwnerVenueImageController;
use Illuminate\Support\Facades\Route;


require __DIR__.'/shatha_routes.php';
require __DIR__.'/zaid.php';


Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::get('/account/signed-in', fn () => response('Signed in successfully.'))
    ->middleware('auth')
    ->name('account.signed-in');


Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
  
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/venues', [AdminVenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}', [AdminVenueController::class, 'show'])->name('venues.show');
    Route::patch('/venues/{venue}/approve', [AdminVenueController::class, 'approve'])->name('venues.approve');
    Route::patch('/venues/{venue}/reject', [AdminVenueController::class, 'reject'])->name('venues.reject');
    Route::patch('/venues/{venue}/status', [AdminVenueController::class, 'updateStatus'])->name('venues.status');
});


Route::prefix('owner')->name('owner.')->middleware(['auth', 'owner'])->group(function () {
    Route::prefix('spaces')->name('spaces.')->group(function () {
        Route::get('/', [OwnerSpaceController::class, 'index'])->name('index');
        Route::get('/create', [OwnerSpaceController::class, 'create'])->name('create');
        Route::post('/', [OwnerSpaceController::class, 'store'])->name('store');
        Route::get('/{space}/edit', [OwnerSpaceController::class, 'edit'])->name('edit');
        Route::put('/{space}', [OwnerSpaceController::class, 'update'])->name('update');
        Route::delete('/{space}', [OwnerSpaceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('space-layouts')->name('space-layouts.')->group(function () {
        Route::get('/', [OwnerSpaceLayoutController::class, 'index'])->name('index');
        Route::get('/create', [OwnerSpaceLayoutController::class, 'create'])->name('create');
        Route::post('/', [OwnerSpaceLayoutController::class, 'store'])->name('store');
       
    });

    Route::prefix('venues')->name('venues.')->group(function () {
        Route::get('/', [OwnerVenueController::class, 'index'])->name('index');
        Route::get('/create', [OwnerVenueController::class, 'create'])->name('create');
        Route::post('/', [OwnerVenueController::class, 'store'])->name('store');
        Route::get('/{venue}', [OwnerVenueController::class, 'show'])->name('show');
        Route::get('/{venue}/edit', [OwnerVenueController::class, 'edit'])->name('edit');
        Route::patch('/{venue}', [OwnerVenueController::class, 'update'])->name('update');
        Route::patch('/{venue}/availability', [OwnerVenueController::class, 'updateAvailability'])->name('availability.update');
        Route::post('/{venue}/images', [OwnerVenueImageController::class, 'store'])->name('images.store');
        Route::delete('/{venue}', [OwnerVenueController::class, 'destroy'])->name('destroy');
    });
});
