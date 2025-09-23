<?php

use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;


Auth::routes();
Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::controller(PasswordResetController::class)->group(function () {
    Route::get('send-otp', 'sendOtp')->name('send-otp');
    Route::post('verify-otp', 'checkOtp')->name('verify-otp');
    Route::get('verify-token/{token}', 'validateToken')->name('verify-token');
    Route::post('update-password', 'updatePassword')->name('update-password');

});
