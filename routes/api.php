<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ReportLogController;
use App\Http\Controllers\Api\ReportSubmissionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\SourceLocationController;
use App\Http\Controllers\Api\WasteEntryController;
use App\Http\Controllers\Api\ProcessedWasteController;
use App\Http\Controllers\Api\WasteOutController;
use App\Http\Controllers\Api\NotificationController;

/*1. ROUTE BEBAS (Bisa Diakses Tanpa Login / Public Routes)*/

// Autentikasi Utama
Route::post('/login', [AuthApiController::class, 'login']);

// Mengambil Data Master / Dropdown untuk Form di Flutter
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sub-categories/{category_id}', [SubCategoryController::class, 'getByCategoryId']);
Route::get('/source-locations', [SourceLocationController::class, 'index']);
Route::get('/processed-waste', [ProcessedWasteController::class, 'index']);
Route::get('/waste-out-methods', [WasteOutController::class, 'index']);
Route::get('/waste-subcategories', [WasteOutController::class, 'getSubcategories']);
Route::get('/waste-b3-notifications', [NotificationController::class, 'getWarnings']);



/*2. ROUTE TERKUNCI (Wajib Login / Protected Routes via Sanctum)*/

Route::middleware('auth:sanctum')->group(function () {

    // Cek Data User yang Sedang Login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Pengaturan Akun PIC
    Route::post('/update-profile', [AuthApiController::class, 'updateProfile']);
    Route::post('/change-password', [AuthApiController::class, 'changePassword']); 

    // Dashboard & Laporan Umum
    Route::get('/dashboard-data', [DashboardApiController::class, 'getDashboardData']);
    Route::get('/laporan-harian', [LaporanController::class, 'index']);
    Route::get('/laporan-harian/{id}', [LaporanController::class, 'show']);

    // Riwayat Gabungan (Personal PIC)
    Route::get('/riwayat-laporan', [ReportLogController::class, 'history']);

    // Transaksi 1: Input Sampah Masuk
    Route::post('/waste-entry', [WasteEntryController::class, 'store']);

    // Transaksi 2: Input Sampah Olahan
    Route::post('/processed-waste-data', [ProcessedWasteController::class, 'store']);

    // Transaksi 3: Input Sampah Keluar
    Route::post('/waste-out', [WasteOutController::class, 'store']);

    // Transaksi 4: Input Laporan Kendala Lapangan
    Route::post('/laporan-kendala', [ReportSubmissionController::class, 'storeKendala']);
    Route::get('/laporan-kendala/{id}', [ReportSubmissionController::class, 'showKendala']); 
    Route::get('/kategori-kendala', [ReportSubmissionController::class, 'getCategories']);

});