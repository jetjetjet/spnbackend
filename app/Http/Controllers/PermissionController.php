<?php

namespace App\Http\Controllers;
use App\HakAkses;
use Auth;
use Validator;
use App\Helpers\Helper;

use App\Http\Repositories\PermissionRepository;
use App\Http\Repositories\AuditTrailRepository;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
  public function getAllPermission(Request $request)
  {
    $responses = Helper::$responses;

    array_push($responses['data'], HakAkses::all());
    $responses['success'] = true;
    $responses['state_code'] = 200;

    return response()->json($responses, $responses['state_code']);
  }

  public function getPositionPermission(Request $request, $idJabatan = null)
  {
    $responses = Helper::$responses;
    
    $userPerm = PermissionRepository::getPositionPerm($idJabatan);
    array_push($responses['data'], $userPerm);
    $responses['success'] = true;
    $responses['state_code'] = 200;

    return response()->json($responses, $responses['state_code']);
  }

  public function savePositionPermission(Request $request, $idJabatan = null)
  {
    $responses = Helper::$responses;
    $rules = array(
      'permissions' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $results['state_code'] = 400;
      $results['messages'] = $validator->messages();
      $results['data'] = $inputs;
      return response()->json($results, $results['state_code']);
    }
    $result = PermissionRepository::savePermission($responses, $idJabatan, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'UpdatePermissions', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
}
