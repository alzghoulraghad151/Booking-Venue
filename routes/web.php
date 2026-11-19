<?php
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Controllers\OwnerSpaceLayoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
    ->middleware(OwnerMiddleware::class)
    ->name('owner.space-layouts.index');

Route::get('/owner/space-layouts/create', [OwnerSpaceLayoutController::class, 'create'])
     ->middleware(OwnerMiddleware::class)
    ->name('owner.space-layouts.create');
    
Route::post('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'store'])
    ->middleware(OwnerMiddleware::class)
    ->name('owner.space-layouts.store');