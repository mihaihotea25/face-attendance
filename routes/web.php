<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');

Route::post('/attendance/recognize', [AttendanceController::class, 'recognize'])->name('attendance.recognize');
