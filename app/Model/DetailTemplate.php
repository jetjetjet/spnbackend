<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class DetailTemplate extends Model
{
  protected $table = 'gen_detailtemplate';
  public $timestamps = false;
  protected $fillable = [
    'template_id',
    'file_id',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}