<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class TemplateSurat extends Model
{
  protected $table = 'gen_template';
  public $timestamps = false;

  protected $fillable = [
    'template_type',
    'template_name',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}