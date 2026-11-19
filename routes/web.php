<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Owner\OwnerSpaceLayoutController;


use App\Http\Controllers\Owner\OwnerSpaceLayoutController;
use App\Http\Middleware\OwnerMiddleware;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
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

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
});
