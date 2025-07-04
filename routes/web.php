<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CanvasController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/canvas', [CanvasController::class, 'index'])->name('canvas');

//Route::post('/invite', [InviteController::class, 'invite'])->name('invite');

Route::get('/invite', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/invite', [RegisterController::class, 'register'])->name('invite.submit');



