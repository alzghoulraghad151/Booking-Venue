<?php
use App\Http\Middleware\OwnerMiddleware;
use App\Http\Controllers\OwnerSpaceLayoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware([OwnerMiddleware::class])->group(function () {
    Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
        ->name('owner.space-layouts.index');

    Route::get('/owner/space-layouts/create', [OwnerSpaceLayoutController::class, 'create'])
        ->name('owner.space-layouts.create');

    Route::post('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'store'])
        ->name('owner.space-layouts.store');

    Route::get('/owner/space-layouts/{id}/edit', [OwnerSpaceLayoutController::class, 'edit'])
        ->whereNumber('id')
        ->name('owner.space-layouts.edit');
});