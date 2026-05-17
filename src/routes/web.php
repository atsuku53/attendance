<?php

use App\Http\Controllers\AdminAuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('index');
    Route::get('/attend_start', [AttendanceController::class, 'attend_start'])->name('attend_start');
    Route::get('/attend_end', [AttendanceController::class, 'attend_end'])->name('attend_end');
    Route::get('/rest_start', [AttendanceController::class, 'rest_start'])->name('rest_start');
    Route::get('/rest_end', [AttendanceController::class, 'rest_end'])->name('rest_end');
    Route::get('/attendance/list', [AttendanceController::class, 'attendance_list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'attendance_detail'])->name('attendance_detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'attendance_modify'])->name('attendance_modify');
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'attendance_request']);
    Route::get('/attendance/detail/add/{day}', [AttendanceController::class, 'attendance_detail_add']);
    Route::post('/attendance/detail/add/{day}', [AttendanceController::class, 'attendance_add']);
});

Route::get('/admin/login', [AdminAuthenticatedSessionController::class, 'create'])
    ->middleware('guest:admin')
    ->name('login_admin');

Route::post('/admin/login', [AdminAuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin', 'throttle:login']);

Route::post('/admin/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:admin')
    ->name('logout_admin');

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/attendance/list', [AttendanceController::class, 'attendance_list_admin'])->name('index_admin');
    Route::get('/admin/attendance/detail/{id}', [AttendanceController::class, 'attendance_detail_admin'])->name('attendance_detail_admin');
    Route::post('/admin/attendance/detail/{id}', [AttendanceController::class, 'attendance_modify_admin']);
    Route::get('/admin/stamp_correction_request/approve/{id}', [AttendanceController::class, 'attendance_approve']);
    Route::get('/admin/stamp_correction_request/list', [AttendanceController::class, 'attendance_request_admin']);
    Route::get('/admin/staff/list', [AttendanceController::class, 'staff_list']);
    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'attendance_staff']);
    Route::post('/admin/attendance/staff/{id}/{month}/export', [AttendanceController::class, 'attendance_export']);
    Route::get('/admin/attendance/detail/add/{id}/{day}', [AttendanceController::class, 'attendance_detail_add_admin']);
    Route::post('/admin/attendance/detail/add/{id}/{day}', [AttendanceController::class, 'attendance_add_admin']);
});
