<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alokasi_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->foreignId('menu_id')->constrained('menu')->onDelete('cascade');
            $table->integer('porsi_dibuat');
            $table->integer('porsi_tersedia');
            $table->timestamps();

            $table->unique(['tanggal', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_harian');
    }
};
