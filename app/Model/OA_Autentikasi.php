<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class oa_autentikasi extends Model {

    public $timestamps = false;
    protected $table = "oa_autentikasi";
    protected $fillable = [
        'autentikasi_token',
        'autentikasi_kadaluarsa',
        'otorisasi_kunci',
    ];

}
