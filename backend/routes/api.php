<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public login & register routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated routes group
Route::middleware('auth:sanctum')->group(function () {
    // Session profile & logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- Admin-only routes ---
    Route::middleware('role.admin')->group(function () {
        // Modul Anggota (Admin CRUD)
        Route::get('/anggota', [AnggotaController::class, 'index']);
        Route::post('/anggota', [AnggotaController::class, 'store']);
        Route::get('/anggota/{id}', [AnggotaController::class, 'show']);
        Route::put('/anggota/{id}', [AnggotaController::class, 'update']);
        Route::patch('/anggota/{id}/nonaktifkan', [AnggotaController::class, 'nonaktifkan']);

        // Modul Buku (Admin CRUD modifications)
        Route::post('/buku', [BukuController::class, 'store']);
        Route::put('/buku/{id}', [BukuController::class, 'update']);
        Route::delete('/buku/{id}', [BukuController::class, 'destroy']);

        // Modul Transaksi Peminjaman (Admin monitoring and payment)
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::patch('/denda/{id}/bayar', [DendaController::class, 'bayar']);

        // Modul Laporan
        Route::get('/laporan/{jenis}', [LaporanController::class, 'generateReport']);
        Route::get('/laporan/{jenis}/pdf', [LaporanController::class, 'exportPdf']);
        Route::get('/dashboard-summary', [LaporanController::class, 'dashboardSummary']);
    });

    // --- Anggota-only routes ---
    Route::middleware('role.anggota')->group(function () {
        // Modul Peminjaman (Anggota checkout)
        Route::post('/peminjaman', [PeminjamanController::class, 'store']);
        Route::get('/peminjaman/status', [PeminjamanController::class, 'status']);
    });

    // --- Shared routes (both Admin and Anggota) ---
    // Catalog view
    Route::get('/buku', [BukuController::class, 'index']);
    Route::get('/buku/{id}', [BukuController::class, 'show']);

    // Returns can be triggered by either member or admin (according to specs)
    Route::post('/pengembalian/{id_peminjaman}', [PengembalianController::class, 'store']);
});
