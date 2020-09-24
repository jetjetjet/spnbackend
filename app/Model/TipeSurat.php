<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TipeSurat extends Model
{
  protected $table = 'gen_tipesurat';
  public $timestamps = false;

  protected $fillable = [
    'tipe_surat',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}