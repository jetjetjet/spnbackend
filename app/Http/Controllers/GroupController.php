<?php

namespace App\Http\Controllers;

use App\Http\Repositories\GroupRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

class GroupController extends Controller
{
  public function getAll(Request $request)
  {
    $respon = Helper::$responses;
    $user = request()->user();
    $isAdmin = $user->tokenCan('is_admin') ? true : false;
    $permissions = Array(
      'unit_edit' => $user->tokenCan('unit_edit') || $isAdmin ? 1 : 0,
      'unit_delete' => $user->tokenCan('unit_delete') || $isAdmin ? 1 : 0
    );
    $result = GroupRepository::groupList($respon, $permissions);

    return response()->json($result, $result['state_code']);
  }

  public function getById(Request $request, $id = null)
  {
    $respon = Helper::$responses;
    $result = GroupRepository::groupById($respon, $id);
    
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $respon = Helper::$responses;
    $rules = array(
      'group_name' => 'required',
      'group_code' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $respon['state_code'] = 400;
      $respon['messages'] = Array($validator->messages()->first());
      $respon['data'] = $inputs;
      return response()->json($respon, $respon['state_code']);
    }

    $result = GroupRepository::save($respon, $id, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function delete(Request $request, $id = null)
  {
    $respon = Helper::$responses;
    $result = GroupRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function searchGroup(Request $request)
	{
		$respon = Helper::$responses;
		//$keyword = $request['keyword'];
		$result = GroupRepository::searchGroup($respon);

		return response()->json($result, $result['state_code']);
  }
}
