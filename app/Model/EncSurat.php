<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EncSurat extends Model
{
  protected $table = 'enc_surat';
  public $timestamps = false;
  protected $fillable = [
    'key',
    'surat_keluar_id',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
