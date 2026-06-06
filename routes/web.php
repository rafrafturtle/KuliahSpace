<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClassScheduleController;
use App\Http\Controllers\CourseClassLeaderController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomAvailabilityController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomRequestController;
use App\Http\Controllers\RoomRequestHistoryController;
use App\Http\Controllers\RoomSearchController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isAn('admin') ? 'dashboard' : 'room-availability.index');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('role:admin')
        ->name('dashboard');

    Route::middleware('role:admin')->group(function (): void {
        Route::resource('buildings', BuildingController::class);
        Route::resource('rooms', RoomController::class);
        Route::resource('courses', CourseController::class);
        Route::resource('semesters', SemesterController::class)->except(['show']);
        Route::resource('academic-years', AcademicYearController::class)
            ->except(['show'])
            ->parameters(['academic-years' => 'academicYear']);
        Route::resource('users', UserController::class)->except(['destroy']);
        Route::get('/users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
        Route::post('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
    });

    Route::resource('schedules', ClassScheduleController::class)
        ->except(['index', 'show'])
        ->middleware('role:admin');
    Route::resource('schedules', ClassScheduleController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,dosen,ketua_kelas,mahasiswa');

    Route::get('/room-availability', [RoomAvailabilityController::class, 'index'])
        ->middleware('role:admin,dosen,ketua_kelas,mahasiswa')
        ->name('room-availability.index');
    Route::get('/room-availability/date/{date}', [RoomAvailabilityController::class, 'dateDetail'])
        ->where('date', '\d{4}-\d{2}-\d{2}')
        ->middleware('role:admin,dosen,ketua_kelas,mahasiswa')
        ->name('room-availability.date');
    Route::post('/room-availability/check', [RoomAvailabilityController::class, 'check'])
        ->middleware('role:admin,dosen,ketua_kelas,mahasiswa')
        ->name('room-availability.check');
    Route::get('/room-search', [RoomSearchController::class, 'index'])
        ->middleware('role:admin,dosen,ketua_kelas,mahasiswa')
        ->name('room-search.index');

    Route::get('/room-request-history', [RoomRequestHistoryController::class, 'index'])
        ->middleware('role:admin')
        ->name('room-request-history.index');
    Route::get('/room-request-history/export-excel', [RoomRequestHistoryController::class, 'exportExcel'])
        ->middleware('role:admin')
        ->name('room-request-history.export-excel');
    Route::get('/room-request-history/export-pdf', [RoomRequestHistoryController::class, 'exportPdf'])
        ->middleware('role:admin')
        ->name('room-request-history.export-pdf');

    Route::get('/room-requests/create', [RoomRequestController::class, 'create'])
        ->middleware('role:admin,dosen,ketua_kelas')
        ->name('room-requests.create');
    Route::post('/room-requests', [RoomRequestController::class, 'store'])
        ->middleware('role:admin,dosen,ketua_kelas')
        ->name('room-requests.store');
    Route::resource('room-requests', RoomRequestController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('role:admin,dosen,ketua_kelas')
        ->parameters(['room-requests' => 'roomRequest']);
    Route::patch('/room-requests/{roomRequest}/approve', [RoomRequestController::class, 'approve'])
        ->middleware('role:admin')
        ->name('room-requests.approve');
    Route::patch('/room-requests/{roomRequest}/reject', [RoomRequestController::class, 'reject'])
        ->middleware('role:admin')
        ->name('room-requests.reject');
    Route::patch('/room-requests/{roomRequest}/cancel', [RoomRequestController::class, 'cancel'])
        ->middleware('role:admin,dosen,ketua_kelas')
        ->name('room-requests.cancel');

    Route::resource('class-leaders', CourseClassLeaderController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->middleware('role:admin,dosen')
        ->parameters(['class-leaders' => 'classLeader']);
});
