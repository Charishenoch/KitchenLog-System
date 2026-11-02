<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $fillable = ['bahan_baku_id','pesan','tgl_kirim','status'];
    protected $casts = ['tgl_kirim' => 'datetime'];

    public function bahanBaku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
}
