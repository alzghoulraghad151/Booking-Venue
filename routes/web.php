<?php
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Controllers\Owner\OwnerVenueImageController;

use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;
require __DIR__.'/shatha_routes.php';
use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\AdminBookingController;


Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');







Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');
});


Route::get('/account/signed-in', fn () => response('Signed in successfully.'))
    ->middleware('auth')
    ->name('account.signed-in');








    Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('venues')->name('venues.')->group(function () {
            Route::get('/', [AdminVenueController::class, 'index'])->name('index');
            Route::get('/{venue}', [AdminVenueController::class, 'show'])->name('show');
            Route::patch('/{venue}/approve', [AdminVenueController::class, 'approve'])->name('approve');
            Route::patch('/{venue}/reject', [AdminVenueController::class, 'reject'])->name('reject');
            Route::patch('/{venue}/status', [AdminVenueController::class, 'updateStatus'])->name('status');
        });
    });














Route::prefix('owner')
    ->name('owner.')
    ->middleware(['auth', 'owner'])
    ->group(function () {
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
            Route::get('/{id}/edit', [OwnerSpaceLayoutController::class, 'edit'])->name('edit');
            Route::patch('/{id}', [OwnerSpaceLayoutController::class, 'update'])->name('update');
        });

        Route::prefix('venues')->name('venues.')->group(function () {
            Route::get('/', [OwnerVenueController::class, 'index'])->name('index');
            Route::get('/create', [OwnerVenueController::class, 'create'])->name('create');
            Route::post('/', [OwnerVenueController::class, 'store'])->name('store');
            Route::get('/{venue}', [OwnerVenueController::class, 'show'])->name('show');
            Route::get('/{venue}/edit', [OwnerVenueController::class, 'edit'])->name('edit');
            Route::patch('/{venue}', [OwnerVenueController::class, 'update'])->name('update');
            Route::patch('/{venue}/availability', [OwnerVenueController::class, 'updateAvailability'])
                ->name('availability.update');
            Route::delete('/{venue}', [OwnerVenueController::class, 'destroy'])->name('destroy');

            Route::post('/{venue}/images', [OwnerVenueImageController::class, 'store'])
                ->name('images.store');
            Route::patch('/{venue}/images/{image}/primary', [OwnerVenueImageController::class, 'setPrimary'])
                ->whereNumber('image')
                ->name('images.primary');
            Route::delete('/{venue}/images/{image}', [OwnerVenueImageController::class, 'destroy'])
                ->whereNumber('image')
                ->name('images.destroy');
        });
    });

require __DIR__ . '/zaid.php';


