<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CanvasController;
use App\Http\Controllers\NotificationTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/canvas', [CanvasController::class, 'index'])->name('canvas');

// Notification Test Routes
Route::get('/notification-test', [NotificationTestController::class, 'index'])->name('notification-test');
Route::post('/notification-test/success', [NotificationTestController::class, 'sendSuccess'])->name('notification-test.success');
Route::post('/notification-test/warning', [NotificationTestController::class, 'sendWarning'])->name('notification-test.warning');
Route::post('/notification-test/danger', [NotificationTestController::class, 'sendDanger'])->name('notification-test.danger');
Route::post('/notification-test/info', [NotificationTestController::class, 'sendInfo'])->name('notification-test.info');
Route::post('/notification-test/clear', [NotificationTestController::class, 'clear'])->name('notification-test.clear');
Route::post('/notification-test/remove', [NotificationTestController::class, 'remove'])->name('notification-test.remove');

Route::post('/invite', [InviteController::class, 'invite'])->name('invite');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
