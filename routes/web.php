<?php
use App\Http\Controllers\OwnerSpaceLayoutController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/owner/space-layouts', [OwnerSpaceLayoutController::class, 'index'])
    ->middleware(['auth', 'role:venue_owner'])
    ->name('owner.space-layouts.index');