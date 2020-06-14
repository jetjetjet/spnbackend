<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class SuratMasuk extends Model
{
  protected $table = 'surat_masuk';
  public $timestamps = false;
  protected $fillable = [
    'file_id',
    'asal_surat',
    'perihal',
    'nomor_surat',
    'tgl_surat',
    'tgl_diterima',
    'lampiran',
    'sifat_surat',
    'klasifikasi',
    'keterangan',
    'prioritas',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
