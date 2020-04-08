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

  public function scopeSaveMenu($query, $id, $result, $inputs, $loginId)
  {
    try{
      $menu = null;
      $mode = "";
      if ($id){
        $menu = $query->where('active', '1')->where('id', $id)->firstOrFail();

        $menu->update([
          'menu_name' => $inputs['menu_name'],
          'display' => $inputs['display'],
          'url' => $inputs['url'],
          'icon' => $inputs['icon'],
          'isparent' => $inputs['isparent'],
          'parent_id' => $inputs['parent_id'],
          'index' => $inputs['index'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginId
        ]);
        $mode = "Ubah";
      } else {
        $menu = $query->create([
          'menu_name' => $inputs['menu_name'],
          'display' => $inputs['display'],
          'url' => $inputs['url'],
          'icon' => $inputs['icon'],
          'isparent' => $inputs['isparent'],
          'parent_id' => $inputs['parent_id'],
          'index' => $inputs['index'],
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginId
        ]);
        $mode = "Simpan";
      }
      $result['success'] = true;
      $result['state_code'] = 200;
      $result['data'] = $menu;
      array_push($result['messages'], trans('messages.succesSaveUpdate', ["item" => $menu->menu_name, "item2" => $mode]));
      return $result;
    } catch(\Exception $e){
      $result['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
      return $result;
    }
  }

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

  public static function mapMenuSideBar($db)
  {
    $temp = new \StdClass();
    $temp->id = isset($db['id']) ? $db['id'] : null;
    $temp->parent_id = isset($db['parent_id']) ? $db['parent_id'] : null;
    $temp->index = isset($db['index']) ? $db['index'] : null;
    $temp->menu_name = isset($db['menu_name']) ? $db['menu_name'] : null;
    $temp->display = isset($db['display']) ? $db['display'] : null;
    $temp->url = isset($db['url']) ? $db['url'] : null;
    $temp->icon = isset($db['icon']) ? $db['icon'] : null;
    $temp->isparent = isset($db['isparent']) ? $db['isparent'] : null;
    if(isset($db->subMenu)){
      $temp->subMenu = $db->subMenu;
    }
    return $temp;
  }
}
