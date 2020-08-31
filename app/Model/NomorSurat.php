<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NomorSurat extends Model
{
  protected $table = 'gen_nomor_surat_keluar';
  public $timestamps = false;
  protected $fillable = [
    'periode',
    'klasifikasi_id',
    'prefix',
    'urut_surat',
    'urut_agenda',
    'no_surat',
    'no_agenda',
    'surat_keluar_id',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}