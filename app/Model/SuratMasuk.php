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
    'to_user_id',
    'is_closed',
    'closed_at',
    'closed_by',
    'tgl_surat',
    'tgl_diterima',
    'lampiran',
    'sifat_surat',
    'klasifikasi_id',
    'keterangan',
    'prioritas',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
