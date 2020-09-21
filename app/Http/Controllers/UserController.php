<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\AuditTrailRepository;
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
    $user = request()->user();
    $isAdmin = $user->tokenCan('is_admin') ? true : false;
    $permissions = Array(
      'user_edit' => $user->tokenCan('user_edit') || $isAdmin ? 1 : 0,
      'user_delete' => $user->tokenCan('user_delete') || $isAdmin ? 1 : 0
    );
    $data = UserRepository::getUserList($filter, $permissions);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $loginid = Auth::user()->getAuthIdentifier();
    $isAdmin = User::checkAdmin($loginid);
    if(($id != null && $id == $loginid) || $isAdmin){
      $result = UserRepository::getUserById($id, $results);
    } else {
      $result = $results;
      $results['messages'] = Array('Tidak dapat melihat profil.');
    }
    return response()->json($result, $result['state_code']);
  }

  public function getProfile(Request $request, $id)
  {
    $results = Helper::$responses;

    if($id == Auth::user()->getAuthIdentifier()){
      $result = UserRepository::getUserById($id, $results);
    } else {
      $result = $results;
      $result['state_code'] = 400;
      array_push($result['messages'], trans('messages.unauthorized'));
    } 

    return response()->json($result, $result['state_code']);
  }

  public function saveProfile(Request $request, $id)
  {
    $results = Helper::$responses;
    
    if($id == Auth::user()->getAuthIdentifier()){
      $rules = array(
        'jenis_kelamin' => 'required',
        'phone' => 'max:15',
      );
  
      $inputs = $request->all();
      $validator = Validator::make($inputs, $rules);
      
      if ($validator->fails()){
        $results['state_code'] = 400;
        $results['messages'] = Array($validator->messages()->first());
        $results['data'] = $inputs;
        return response()->json($results, 400);
      }
  
      $result = UserRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());
      $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());
    } else {
      $result = $results;
      $result['state_code'] = 400;
      array_push($result['messages'], trans('messages.unauthorized'));
    } 

    return response()->json($result, $result['state_code']);
  }

  
  public function changeProfilePassword(Request $request, $id = null)
  {
    $results = Helper::$responses;

    if($id != Auth::user()->getAuthIdentifier())
    {
      $result = $results;
      $result['state_code'] = 400;
      array_push($result['messages'], trans('messages.unauthorized'));
      return response()->json($result, $result['state_code']);
    }  

    $rules = array(
      'password' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $results['state_code'] = 400;
      $results['messages'] = Array($validator->messages()->first());
      $results['data'] = $inputs;
      return response()->json($results, $results['state_code']);
    }

    $result = UserRepository::changePassword($id, $results, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'ChangePassword', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
  
  public function uploadProfileFoto(Request $request, $id)
  {
    $respon = Helper::$responses;
    $user = request()->user(); 

    if($id != Auth::user()->getAuthIdentifier())
    {
      $respon['state_code'] = 400;
      array_push($respon['messages'], trans('messages.unauthorized'));
      return response()->json($result, $result['state_code']);
    }  
    // Validation rules.
    $rules = array(
      'file' => 'required|image|max:1024|mimes:jpeg,bmp,png,gif',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    // Validation fails?
    if ($validator->fails()){
      $respon['state_code'] = 400;
      $respon['messages'] = Array($validator->messages()->first());
      $respon['data'] = $inputs;
      return response()->json($respon, $respon['state_code']);
    }
    
    $result = UserRepository::saveFoto($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'UploadPhoto', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $loginid = Auth::user()->getAuthIdentifier();
    $isAdmin = User::checkAdmin($loginid);
    if(($id != null && $id == $loginid) || $isAdmin){
      $rules = array(
        'jenis_kelamin' => 'required',
        'phone' => 'max:15'
      );
      
      if(!$id){
        $rules['position_id'] = 'required';
        $rules['full_name'] = 'required';
        $rules['password'] = 'required';
        $rules['username'] = 'required';
        $rules['nip'] = 'required|unique:gen_user|max:18';
        $rules['email'] = 'required';
        $rules['jenis_kelamin'] = 'required';
        $rules['phone'] = 'max:15';
      }
      $inputs = $request->all();
      $validator = Validator::make($inputs, $rules);
      
      if ($validator->fails()){
        $results['state_code'] = 500;
        $results['messages'] = Array($validator->messages()->first());
        $results['data'] = $inputs;
        return response()->json($results, $results['state_code']);
      }
  
      $result = UserRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());
      $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());
    } else {
      $result = $results;
      $results['messages'] = Array('Tidak dapat mengautorisasi.');
    }
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
      $results['messages'] = Array($validator->messages()->first());
      $results['data'] = $inputs;
      return response()->json($results, $results['state_code']);
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
  
  public function searchUserSM(Request $request)
  {
    $respon = Helper::$responses;
		$result = UserRepository::searchUserSuratMasuk($respon, Auth::user()->getAuthIdentifier());

		return response()->json($result, $result['state_code']);
  }

  public function searchUserSK(Request $request)
  {
    $respon = Helper::$responses;
    $user = request()->user();
    $permission = '';
    if($user->tokenCan('suratKeluar_approve')){
      $permission = 'suratKeluar_approve';
    } else if ($user->tokenCan('suratKeluar_agenda')){
      $permission = 'suratKeluar_agenda';
    } else if ($user->tokenCan('suratKeluar_verify')){
      $permission = 'suratKeluar_verify';
    } else if ($user->tokenCan('suratKeluar_save')){
      $permission = 'suratKeluar_save';
    } else {
      //
    }
		$result = UserRepository::searchUserSuratKeluar($respon, $permission, Auth::user()->getAuthIdentifier());

		return response()->json($result, $result['state_code']);
  }

  public function searchUserTtd(Request $request)
  {
    $respon = Helper::$responses;
		$result = UserRepository::searchUserTtd($respon, Auth::user()->getAuthIdentifier());

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
      $respon['messages'] = Array($validator->messages()->first());
      $respon['data'] = $inputs;
      return response()->json($respon, $respon['state_code']);
    }
    
    $result = UserRepository::saveFoto($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'UploadPhoto', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
  
  public function createIdTtd(Request $request, $id)
  {
    $respon = Helper::$responses;
    $result = UserRepository::createIdTtd($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'CreateIDTTD', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function simpanTTD(Request $request, $id)
  {
    $respon = Helper::$responses;
    $rules = array(
      'file' => 'required|image|mimes:png',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

		// Validation fails?
		if ($validator->fails()){
			$respon['state_code'] = 400;
      $respon['messages'] = Array($validator->messages()->first());
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }

    $result = UserRepository::saveTTD($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'CreateTTD', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

}
