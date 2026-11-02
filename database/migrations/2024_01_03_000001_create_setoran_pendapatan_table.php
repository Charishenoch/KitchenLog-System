<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('setoran_pendapatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->decimal('jumlah', 14, 2);
            $table->date('tanggal_setor');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending','diterima'])->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('setoran_pendapatan'); }
};
