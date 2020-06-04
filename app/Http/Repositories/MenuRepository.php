<?php

namespace app\Http\Repositories;

use App\Model\Menu;
use DB;
use Exception;

class MenuRepository
{
  public static function menuList($result)
  {
    $q = Menu::where('active', '1')->get();
    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $q;

    return $result;
  }

  public static function menuById($result, $id)
  {
    $q = Menu::getById($id)->first();
    if ($q == null){
      $result['state_code'] = 400;
      $result['messages'] = trans('messages.dataNotFound', ["item" => $id]);
    } else {
      $result['state_code'] = 200;
      $result['success'] = true;
      $result['data'] = $q;
    }
    return $result;
  }

  public static function menuSideBar($result)
  {
    $q = Menu::sideBar();
    $pMenu = $q->where('isparent', '1')->get();
    foreach($pMenu as $m1){
      $tempMenu = new \StdClass();
      $tempMenu = $m1;
      if($m1['isparent']){
        $tempSubMenu = Array();
        $subMenu = Menu::sideBar()->where('parent_id', $m1['id'])->get();
        foreach($subMenu as $sm){
          array_push($tempSubMenu, self::mapMenuSideBar($sm));
        }
        $tempMenu->subMenu = $tempSubMenu;
      }
      array_push($result['data'], self::mapMenuSideBar($tempMenu));
    }
    $result['success'] = true;
    $result['state_code']= 200;
    
    return $result;
  }

  public static function save($result, $id, $inputs, $loginId)
  {
    try{
      $menu = null;
      $mode = "";
      if ($id){
        $menu = Menu::where('active', '1')->where('id', $id)->firstOrFail();

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
        $menu = Menu::create([
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
    } catch(\Exception $e){
      $result['state_code'] = 500;
      $result['data'] = $inputs;
      array_push($result['messages'], $e->getMessage());
    }
    return $result;
  }

  public static function delete($result, $id, $loginid)
  {
    try{
      $menu = Menu::getById($id)->firstOrFail();

      $menu->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $menu->menu_name]));
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
    return $result;
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