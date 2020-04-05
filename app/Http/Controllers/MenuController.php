<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HakAkses;
use App\Helpers\Helper;
use App\Model\Menu;
use Auth;
use Validator;
use DB;

class MenuController extends Controller
{
  public function getAllMenuPermission()
  {
    $perm = HakAkses::all();$dw = Auth::user()->getAuthIdentifier();
    return response()->json($perm, 200);
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
    $q = Menu::where('active', '1')
      ->where('gen_menu.id', $id)
      ->first();
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

    $result = Helper::$responses;
    try{
      $userLogin = Auth::user()->getAuthIdentifier();
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
          'modified_by' => $userLogin
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
          'created_by' => $userLogin
        ]);
        $mode = "Simpan";
      }
      $result['success'] = true;
      $result['state_code'] = 200;
      $result['data'] = $menu;
      array_push($result['messages'], trans('messages.succesSaveUpdate', ["item" => $menu->menu_name, "item2" => $mode]));
      return response()->json($result, 200);
    } catch(\Exception $e){
      $result['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }

  public function delete(Request $request, $id = null)
  {
    $result = Helper::$responses;
    try{
      $menu = Menu::where('active', '1')->where('id', $id)->firstOrFail();

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
