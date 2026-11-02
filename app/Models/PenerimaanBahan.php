<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBahan extends Model
{
    protected $table = 'penerimaan_bahan';
    protected $fillable = ['bahan_baku_id','staf_id','jumlah','sisa','tgl_masuk','tgl_kadaluarsa'];
    protected $casts = ['tgl_masuk' => 'date', 'tgl_kadaluarsa' => 'date', 'jumlah' => 'float', 'sisa' => 'float'];

    public function bahanBaku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
    public function staf() { return $this->belongsTo(User::class, 'staf_id'); }
}
