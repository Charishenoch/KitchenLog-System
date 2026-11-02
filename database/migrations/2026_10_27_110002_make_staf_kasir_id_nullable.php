<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubah kolom staf_id dan kasir_id menjadi nullable di semua tabel terkait.
 * Ini agar histori data (penerimaan, limbah, transaksi, setoran) tetap tersimpan
 * meski user/staf yang bersangkutan sudah dihapus dari sistem.
 */
return new class extends Migration
{
    public function up(): void
    {
        // penerimaan_bahan.staf_id → nullable
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->unsignedBigInteger('staf_id')->nullable()->change();
        });

        // limbah.staf_id → nullable
        Schema::table('limbah', function (Blueprint $table) {
            $table->unsignedBigInteger('staf_id')->nullable()->change();
        });

        // transaksi.kasir_id → nullable
        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('kasir_id')->nullable()->change();
        });

        // setoran_pendapatan.kasir_id → nullable
        Schema::table('setoran_pendapatan', function (Blueprint $table) {
            $table->unsignedBigInteger('kasir_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->unsignedBigInteger('staf_id')->nullable(false)->change();
        });

        Schema::table('limbah', function (Blueprint $table) {
            $table->unsignedBigInteger('staf_id')->nullable(false)->change();
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('kasir_id')->nullable(false)->change();
        });

        Schema::table('setoran_pendapatan', function (Blueprint $table) {
            $table->unsignedBigInteger('kasir_id')->nullable(false)->change();
        });
    }
};
