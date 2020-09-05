<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class DisSuratKeluar extends Model
{
  protected $table = 'dis_surat_keluar';
  public $timestamps = false;
  protected $fillable = [
    'surat_keluar_id',
    'tujuan_user_id',
    'file_id',
    'is_read',
    'last_read',
    'keterangan',
    'log',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
