<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\UserRepository;
use Auth;
use App\Helpers\Helper;
use Validator;
use DB;

class UserController extends Controller
{
  public function getAll(Request $request)
  {
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = UserRepository::getUserList($filter);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $result = UserRepository::getUserById($id, $results);
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $rules = array(
      'username' => 'required',
      'email' => 'required',
      'phone' => 'max:15',
      'password' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);
    
    if ($validator->fails()){
      $results['state_code'] = 400;
      $results['messages'] = $validator->messages();
      $results['data'] = $inputs;
      return response()->json($results, 400);
    }

    $result = UserRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());    
    return response()->json($result, $results['state_code']);
  }

  public function delete(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $result = UserRepository::deleteUserById($id, $results, Auth::user()->getAuthIdentifier());
    
    return response()->json($result, $result['state_code']);
  }

  public function changePassword(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $rules = array(
      'password' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $results['state_code'] = 400;
      $results['messages'] = $validator->messages();
      $results['data'] = $inputs;
      return response()->json($results, 400);
    }

    $result = UserRepository::changePassword($id, $results, $inputs, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }

}
