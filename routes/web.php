<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\WasteCategoryController;
use App\Http\Controllers\Admin\WasteSubCategoryController;
use App\Http\Controllers\Admin\WasteB3Controller;
use App\Http\Controllers\Admin\ProcessedWasteController;
use App\Http\Controllers\Admin\UnitMeasuredController;
use App\Http\Controllers\Admin\SourceLocationController;
use App\Http\Controllers\Admin\CollectorBuyerController;
use App\Http\Controllers\Admin\WasteEntryController;
use App\Http\Controllers\Admin\WasteOutController;
use App\Http\Controllers\Admin\WasteOutMethodController;
use App\Http\Controllers\Admin\ProcessedWasteDataController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Kelola Pengguna (PIC)
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Data Master
    Route::resource('waste-category', WasteCategoryController::class);
    Route::resource('waste-subcategory', WasteSubCategoryController::class);
    Route::resource('waste-b3', WasteB3Controller::class);
    Route::resource('processed-waste', ProcessedWasteController::class);
    Route::resource('unit-measured', UnitMeasuredController::class);
    Route::resource('source-location', SourceLocationController::class);
    Route::resource('collector-buyer', CollectorBuyerController::class);
    Route::resource('waste-out-method', WasteOutMethodController::class)->only(['index', 'store', 'update', 'destroy']);

    // Monitoring
    Route::resource('waste-entry', WasteEntryController::class)->only(['index', 'show']);
    Route::resource('waste-out', WasteOutController::class)->only(['index', 'show', 'store']);
    Route::resource('processed-waste-data', ProcessedWasteDataController::class)->only(['index', 'show']);
    Route::resource('report', ReportController::class)->only(['index', 'show']);
});
