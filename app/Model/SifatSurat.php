<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SifatSurat extends Model
{
  protected $table = 'gen_sifatsurat';
  public $timestamps = false;

  protected $fillable = [
    'sifat_surat',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
