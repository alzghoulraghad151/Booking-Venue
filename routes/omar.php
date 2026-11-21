<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\FavoriteSpaceController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Owner\OwnerSpaceController;
use App\Http\Controllers\Owner\OwnerVenueController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicSpaceSearchController;
use App\Http\Controllers\SpaceVoiceSearchController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicHomeController::class, 'index'])->name('home');

Route::get('/spaces/search', [PublicSpaceSearchController::class, 'index'])
    ->name('spaces.search');

Route::post('/spaces/{space}/favorite', [FavoriteSpaceController::class, 'toggle'])
    ->middleware(['auth', 'organizer'])
    ->name('spaces.favorite.toggle');

Route::post('/spaces/search/voice', [SpaceVoiceSearchController::class, 'transcribe'])
    ->middleware('throttle:5,1')
    ->name('spaces.search.voice');

Route::get('/spaces/search/summary-audio', \App\Http\Controllers\SpaceSearchAudioController::class)
    ->middleware(['signed', 'throttle:10,1'])
    ->name('spaces.search.summary-audio');

Route::prefix('account')->name('account.')->middleware('auth')->group(function () {
    Route::get('/personal-details', [ProfileController::class, 'edit'])->name('personal-details.edit');
    Route::patch('/personal-details', [ProfileController::class, 'update'])->name('personal-details.update');
    Route::delete('/profile-photo', [ProfileController::class, 'destroyPhoto'])->name('profile-photo.destroy');
    Route::post('/profile-photo', [ProfileController::class, 'updatePhoto'])->name('profile-photo.update');
    Route::patch('/email', [ProfileController::class, 'updateEmail'])->name('email.update');
    Route::view('/settings', 'account.settings')->name('settings');
    Route::patch('/settings/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:5,1')->name('password.update');
    Route::delete('/settings/account', [ProfileController::class, 'destroyAccount'])
        ->middleware('throttle:5,1')->name('destroy');
    Route::get('/favourites', [FavouriteController::class, 'index'])->name('favourites.index');
    Route::post('/favourites/{space}', [FavouriteController::class, 'store'])
        ->whereNumber('space')->name('favourites.store');
    Route::delete('/favourites/{space}', [FavouriteController::class, 'destroy'])
        ->whereNumber('space')->name('favourites.destroy');
});
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
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])
        ->name('users.status');
});
