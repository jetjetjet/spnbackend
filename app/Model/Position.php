<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
  protected $table = 'gen_position';
  public $timestamps = false;
  protected $fillable = [
    'group_id',
    'position_name',
    'position_type',
    'active',
    'detail',
    'is_parent',
    'parent_id',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}