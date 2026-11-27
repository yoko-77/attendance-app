<?php

use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\ApplicationDetailController;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return redirect('/login');
});

Route::view('/admin/login', 'admin.admin-login')->middleware('guest');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->middleware(['guest', 'throttle:login']);
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');

Route::controller(UserAttendanceController::class)->middleware(['auth', 'can:general'])->group(function () {
    Route::get('/attendance', 'create');
    Route::post('/attendance', 'store');
    Route::get('/attendance/list', 'index');
});

Route::controller(ApplicationDetailController::class)->middleware('auth')->group(function () {
    Route::get('/attendance/{id}', 'show');
    Route::post('/attendance/{id}', 'store');
    Route::get('/stamp_correction_request/list', 'index');
    Route::get('/application/{id}', 'showApplication');
});

// Route::controller(AdminStaffAttendanceController::class)->group(function () {
//    Route::get('/URL', 'メソッド名');
//    Route::post('/URL', 'メソッド名');});
