<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupMenu extends Model
{
  protected $table = 'gen_groupmenu';
  public $timestamps = false;
  protected $fillable = ['group_id',
    'menu_id',
    'permissions',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
