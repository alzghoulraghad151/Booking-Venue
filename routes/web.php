<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Controllers\Owner\OwnerVenueImageController;
use App\Http\Controllers\PublicSpaceSearchController;
use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminVenueController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\SpaceVoiceSearchController;
use App\Http\Controllers\BookingInvitationController;
use App\Http\Controllers\PublicVenueController;

use App\Http\Controllers\PublicSpaceDetailsController;



Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');



    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
    ->middleware('throttle:5,1')
    ->name('password.email');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])
    ->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:5,1')
    ->name('password.update');
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

Route::get('/spaces/search', [PublicSpaceSearchController::class, 'index'])
    ->name('spaces.search');


    Route::post('/spaces/search/voice', [SpaceVoiceSearchController::class, 'transcribe'])
    ->middleware('throttle:5,1')
    ->name('spaces.search.voice');


    Route::get('/spaces/search/summary-audio', \App\Http\Controllers\SpaceSearchAudioController::class)
    ->middleware(['signed', 'throttle:10,1'])
    ->name('spaces.search.summary-audio');


 Route::get('/spaces/{space}', [PublicSpaceDetailsController::class, 'show'])
    ->whereNumber('space')
    ->name('spaces.show');

Route::get('/venues/{venue}', [PublicVenueController::class, 'show'])
    ->whereNumber('venue')
    ->name('public.venues.show');


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




Route::middleware('auth')->group(function (): void {
    Route::view('/booking-assistant', 'public.bookings.from-invite')
        ->name('bookings.from-invite');

    Route::post('/booking-assistant', [BookingInvitationController::class, 'analyze'])
        ->middleware('throttle:5,1')
        ->name('bookings.from-invite.analyze');
    Route::post('/booking-assistant/suggestions', [BookingInvitationController::class, 'suggest'])
        ->middleware('throttle:15,1')
        ->name('bookings.from-invite.suggest');
});



Route::post('/my-bookings/{booking}/checkout', \App\Http\Controllers\Users\StripeCheckoutController::class)
    ->middleware(['auth', 'organizer', 'throttle:10,1'])
    ->whereNumber('booking')
    ->name('my-bookings.checkout');

Route::post('/stripe/booking-webhook', \App\Http\Controllers\StripeBookingWebhookController::class)
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

require __DIR__ . '/zaid.php';
require __DIR__ . '/omar.php';
require __DIR__ . '/yousef.php';
require __DIR__.'/shatha_routes.php';
