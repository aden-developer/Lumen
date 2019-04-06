<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OA_Otorisasi extends Model {

    public $timestamps = false;
    protected $table = "oa_otorisasi";
    protected $fillable = [
        'otorisasi_kunci',
        'otorisasi_alamat',
        'otorisasi_password',
        'otorisasi_aplikasi',
        'otorisasi_pemilik',
        'otorisasi_keterangan',
        'otorisasi_tanggal_dibuat',
        'otorisasi_perbaikan_api',
        'nama_pengguna_api',
    ];

}
