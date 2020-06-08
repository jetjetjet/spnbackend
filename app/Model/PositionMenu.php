<?php

namespace App;

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class PositionMenu extends Model
{
  protected $table = 'gen_positionmenu';
  public $timestamps = false;
  protected $fillable = [
    'position_id',
    'menu_id',
    'permissions',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}