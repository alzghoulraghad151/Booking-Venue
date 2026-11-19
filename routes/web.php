<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');