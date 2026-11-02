<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah harga & deskripsi ke bahan_baku
        Schema::table('bahan_baku', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_baku', 'harga')) {
                $table->decimal('harga', 12, 2)->default(0)->after('batas_minimum');
            }
            if (!Schema::hasColumn('bahan_baku', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('harga');
            }
        });

        // Tambah deskripsi & gambar ke menu
        Schema::table('menu', function (Blueprint $table) {
            if (!Schema::hasColumn('menu', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('menu', 'tersedia')) {
                $table->boolean('tersedia')->default(true)->after('deskripsi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->dropColumnIfExists('harga');
            $table->dropColumnIfExists('deskripsi');
        });
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumnIfExists('deskripsi');
            $table->dropColumnIfExists('tersedia');
        });
    }
};
