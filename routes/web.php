<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::get('/', function () {
    return redirect()->route('attendance');
});

Route::middleware('auth')->group(function () {
    Route::get('/attendance', function () {
        return view('attendance.index');
    });

    Route::get('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/recognize', [AttendanceController::class, 'recognize'])->name('attendance.recognize');
});

