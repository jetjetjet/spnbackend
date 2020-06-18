<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\AuditTrailRepository;
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
    if(!$id){
      $rules['password'] = 'required';
      $rules['nip'] = 'required';
      $rules['email'] = 'required';
      $rules['nip'] = 'required';
    }
    $rules = array(
      'jenis_kelamin' => 'required',
      'phone' => 'max:15',
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
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());
  
    return response()->json($result, $result['state_code']);
  }

  public function delete(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $result = UserRepository::deleteUserById($id, $results, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

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
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'ChangePassword', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function searchUser(Request $request)
  {
    $respon = Helper::$responses;
		//$keyword = $request['keyword'];
		$result = UserRepository::searchUser($respon);

		return response()->json($result, $result['state_code']);
  }

  public function uploadFoto(Request $request, $id)
  {
    $respon = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'file' => 'required|image|max:1024|mimes:jpeg,bmp,png,gif',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

		// Validation fails?
		if ($validator->fails()){
			$respon['state_code'] = 400;
      $respon['messages'] = $validator->messages();
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }
    
    $result = UserRepository::saveFoto($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'UploadPhoto', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

}
