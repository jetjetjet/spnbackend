<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DisSuratMasuk extends Model
{
  protected $table = 'dis_surat_masuk';
  public $timestamps = false;
  protected $fillable = [
    'surat_masuk_id',
    'to_user_id',
    'is_tembusan',
    'arahan',
    'is_private',
    'is_read',
    'last_read',
    'log',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
} 
