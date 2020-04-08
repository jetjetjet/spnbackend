<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HakAkses;
use App\Helpers\Helper;
use App\Model\Menu;
use App\User;
use App\Model\Group;
use App\Model\GroupMenu;
use Auth;
use Validator;
use DB;

class MenuController extends Controller
{
  public function getAllMenuPermission()
  {
    $perm = HakAkses::all();
    return response()->json($perm, 200);
  }

  public function getMenuSideBar()
  {
    $result = Helper::$responses;
    $q = Menu::sideBar();
    $pMenu = $q->where('isparent', '1')->get();
    foreach($pMenu as $m1){
      $tempMenu = new \StdClass();
      $tempMenu = $m1;
      if($m1['isparent']){
        $tempSubMenu = Array();
        $subMenu = Menu::sideBar()->where('parent_id', $m1['id'])->get();
        foreach($subMenu as $sm){
          array_push($tempSubMenu, Menu::mapMenuSideBar($sm));
        }
        $tempMenu->subMenu = $tempSubMenu;
      }
      array_push($result['data'], Menu::mapMenuSideBar($tempMenu));
    }
    $result['success'] = true;
    $result['state_code']= 200;
    return response()->json($result, $result['state_code']);
  }

  public function getAll(Request $request)
  {
    $result = Helper::$responses;
    $q = Menu::where('active', '1')->get();
    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $q;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $result = Helper::$responses;
    $q = Menu::getById($id)->first();
    if ($q == null){
      $result['state_code'] = 400;
      $result['messages'] = trans('messages.dataNotFound', ["item" => $id]);
    } else {
      $result['state_code'] = 200;
      $result['success'] = true;
      $result['data'] = $q;
    }
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $rules = array(
      'menu_name' => 'required',
      'display' => 'required',
      'url' => 'required',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $result['state_code'] = 400;
      $result['messages'] = $validator->messages();
      $result['data'] = $inputs;
      return response()->json($result, 400);
    }

    $respon = Helper::$responses;
    $result = Menu::saveMenu($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
    
  }

  public function delete(Request $request, $id = null)
  {
    $result = Helper::$responses;
    try{
      $menu = Menu::getById($id)->firstOrFail();

      $menu->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => Auth::user()->getAuthIdentifier()
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $menu->menu_name]));
      return response()->json($result, 200);
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }
}
