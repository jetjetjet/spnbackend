<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
  protected $table = 'gen_menu';
  public $timestamps = false;
  protected $fillable = ['menu_name',
    'display',
    'url',
    'icon',
    'isparent',
    'parent_id',
    'index',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
