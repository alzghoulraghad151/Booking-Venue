<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerVenueController;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.space-layouts.index');

Route::get('/owner/space-layouts/create', [OwnerSpaceLayoutController::class, 'create'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.space-layouts.create');

Route::post('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'store'])
    ->middleware(['auth', OwnerMiddleware::class])
    ->name('owner.space-layouts.store');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');