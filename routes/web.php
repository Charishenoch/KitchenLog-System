<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\DapurController;
use App\Http\Controllers\AlokasiController;

Route::get('/', fn() => redirect()->route('login'));

// ─── Guest only (belum login) ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

// ─── Auth required (sudah login) ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Logout bisa dilakukan oleh semua role
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── Admin only ──────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Kelola Menu
        Route::resource('menu', MenuController::class)->except(['show']);

        // Kelola Resep (berdasarkan menu_id)
        Route::get('/resep',               [ResepController::class, 'index'])->name('resep.index');
        Route::get('/resep/tambah',        [ResepController::class, 'create'])->name('resep.create');
        Route::post('/resep',              [ResepController::class, 'store'])->name('resep.store');
        Route::get('/resep/{menuId}/edit', [ResepController::class, 'edit'])->name('resep.edit');
        Route::put('/resep/{menuId}',      [ResepController::class, 'update'])->name('resep.update');
        Route::delete('/resep/{menuId}',   [ResepController::class, 'destroy'])->name('resep.destroy');

        // Kelola Bahan Baku
        Route::resource('bahan-baku', BahanBakuController::class)->except(['show']);

        // Stok Inventaris
        Route::get('/stok',         [StokController::class, 'index'])->name('stok.index');
        Route::post('/stok/terima', [StokController::class, 'terima'])->name('stok.terima');

        // Notifikasi
        Route::get('/notifikasi',             [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::post('/notifikasi/{id}/baca',  [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
        Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
        Route::delete('/notifikasi/{id}',     [NotifikasiController::class, 'destroy'])->name('notifikasi.destroy');

        // Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

        // Kelola Pengguna
        Route::resource('pengguna', PenggunaController::class)->except(['show']);

        // Kelola Setoran dari Kasir
        Route::prefix('setoran')->name('setoran.')->group(function () {
            Route::get('/',             [SetoranController::class, 'index'])->name('index');
            Route::post('/{id}/terima', [SetoranController::class, 'terima'])->name('terima');
        });
    });

    // ─── Kasir only ──────────────────────────────────────────────────────────
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/',                      [KasirController::class, 'index'])->name('dashboard');
        Route::post('/proses',               [KasirController::class, 'proses'])->name('proses');
        Route::post('/setor',                [KasirController::class, 'setor'])->name('setor');
        Route::get('/riwayat',               [KasirController::class, 'riwayat'])->name('riwayat');
        Route::get('/stok',                  [KasirController::class, 'stok'])->name('stok');
        Route::get('/notifikasi',            [KasirController::class, 'notifikasi'])->name('notifikasi');
        Route::post('/notifikasi/{id}/baca', [KasirController::class, 'bacaNotif'])->name('notifikasi.baca');
    });

    // ─── Staf Dapur only ─────────────────────────────────────────────────────
    Route::middleware('role:dapur')->prefix('dapur')->name('dapur.')->group(function () {
        Route::get('/',                         [DapurController::class, 'dashboard'])->name('dashboard');
        Route::get('/stok',                     [DapurController::class, 'stok'])->name('stok');
        Route::get('/alokasi',                  [AlokasiController::class, 'index'])->name('alokasi.index');
        Route::post('/alokasi',                 [AlokasiController::class, 'store'])->name('alokasi.store');
        Route::put('/alokasi/{id}',             [AlokasiController::class, 'update'])->name('alokasi.update');
        Route::get('/penerimaan',               [DapurController::class, 'penerimaan'])->name('penerimaan');
        Route::post('/penerimaan',              [DapurController::class, 'simpanPenerimaan'])->name('penerimaan.store');
        Route::get('/limbah',                   [DapurController::class, 'limbah'])->name('limbah');
        Route::post('/limbah',                  [DapurController::class, 'simpanLimbah'])->name('limbah.store');
        Route::get('/notifikasi',               [DapurController::class, 'notifikasi'])->name('notifikasi');
        Route::post('/notifikasi/{id}/baca',    [DapurController::class, 'bacaNotif'])->name('notifikasi.baca');
        Route::post('/notifikasi/baca-semua',   [DapurController::class, 'bacaSemuaNotif'])->name('notifikasi.baca-semua');
        Route::get('/riwayat',                  [DapurController::class, 'riwayat'])->name('riwayat');
    });
});
