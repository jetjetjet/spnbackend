<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HakAkses;
use App\Helpers\Helper;
use App\Http\Repositories\MenuRepository;
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
    $results = Helper::$responses;
    $result = MenuRepository::menuSideBar($results);
    
    return response()->json($result, $result['state_code']);
  }

  public function getAll(Request $request)
  {
    $results = Helper::$responses;
    $result = MenuRepository::menuList($results);
    
    return response()->json($result, $result['state_code']);
  }

  public function getById(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $result = MenuRepository::menuById($results, $id);
    
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $rules = array(
      'menu_name' => 'required',
      'display' => 'required',
      'url' => 'required',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $results['state_code'] = 400;
      $results['messages'] = $validator->messages();
      $results['data'] = $inputs;
      return response()->json($results, $results['state_code']);
    }

    $result = MenuRepository::save($results, $id, $inputs, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }

  public function delete(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $result = MenuRepository::delete($results, $id, Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
}
