<?php
use App\Http\Controllers\OwnerSpaceLayoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\OwnerVenueController;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.space-layouts.index');


    Route::get('/owner/venues', [OwnerVenueController::class, 'index'])
    ->middleware(['auth', 'owner'])
    ->name('owner.venues.index');
