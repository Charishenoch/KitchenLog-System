<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Limbah extends Model
{
    protected $table = 'limbah';
    protected $fillable = ['tanggal','bahan_baku_id','jumlah','alasan','staf_id'];
    protected $casts = ['tanggal' => 'datetime', 'jumlah' => 'float'];

    public function bahanBaku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
    public function staf() { return $this->belongsTo(User::class, 'staf_id'); }
}
