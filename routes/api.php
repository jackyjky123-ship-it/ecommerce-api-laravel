<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//public route
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

//Protected Route
Route::middleware('auth:sanctum')->group(function (){
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
