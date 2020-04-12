<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
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
    $data = User::getUserList($filter);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $result = Helper::$responses;
    $q = User::where('active', '1')
      ->where('gen_user.id', $id)
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
      'username' => 'required',
      'email' => 'required|exists:gen_user,email',
      'phone' => 'max:15',
      'password' => 'required'
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
      $user = null;
      $mode = "";
      if ($id){
        $user = User::where('active', '1')->where('id', $id)->firstOrFail();

        $user->update([
          'full_name' => $inputs['full_name'],
          'phone' => $inputs['phone'],
          'address' => $inputs['address'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $userLogin
        ]);
        $mode = "Ubah";
      } else {
        $user = User::create([
          'username' => $inputs['username'],
          'password' => bcrypt($inputs['password']),
          'email' => $inputs['email'],
          'full_name' => $inputs['full_name'],
          'phone' => $inputs['phone'],
          'address' => $inputs['address'],
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $userLogin
        ]);
        $mode = "Simpan";
      }
      $result['success'] = true;
      $result['state_code'] = 200;
      $result['data'] = $user;
      array_push($result['messages'], trans('messages.succesSaveUpdate', ["item" => $user->username, "item2" => $mode]));
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
      $user = User::where('active', '1')->where('id', $id)->firstOrFail();

      $user->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => Auth::user()->getAuthIdentifier()
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $user->username]));
      return response()->json($result, 200);
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }

  public function changePassword(Request $request, $id = null)
  {
    $rules = array(
      'password' => 'required'
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
      $user = User::where('active', '1')->where('id', $id)->firstOrFail();

      $user->update([
        'password' => bcrypt($inputs['password']),
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => Auth::user()->getAuthIdentifier()
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successUpdatePassword', ["item" => $user->username]));
      return response()->json($result, 200);
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }

}
