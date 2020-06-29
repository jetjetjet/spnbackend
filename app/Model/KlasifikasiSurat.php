<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiSurat extends Model
{
    protected $table = 'gen_klasifikasi_surat';
    public $timestamps = false;
    protected $fillable = ['kode_klasifikasi',
      'nama_klasifikasi',
      'detail',
      'active',
      'created_at',
      'created_by',
      'modified_at',
      'modified_by'
    ];
}
