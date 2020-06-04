<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

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

  public function scopeGetById($query, $id)
  {
    return $query->where('active', '1')->where('gen_menu.id', $id);
  }

  public function scopeSideBar($query)
  {
    return $query->join('gen_groupmenu as gm', 'gen_menu.id', 'gm.menu_id')
      ->join('gen_group as gg', 'gg.id', 'gm.group_id')
      ->join('gen_user as u', 'u.group_id', 'gg.id')
      ->where([
        "gen_menu.active" => '1',
        "gg.active" => '1',
        "gm.active" => '1',
        "u.active" => '1',
        "u.id" => Auth::user()->getAuthIdentifier()
      ])
      ->orderBy('index')
      ->select('gen_menu.id','parent_id', 'index', 'menu_name', 'display', 'url', 'icon', 'isparent');
  }
}
