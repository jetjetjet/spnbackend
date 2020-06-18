<?php

namespace App\Http\Controllers;
use App\HakAkses;
use Auth;
use Validator;
use App\Helpers\Helper;

use App\Http\Repositories\PermissionRepository;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
  public function getPositionPermission(Request $request, $idJabatan = null)
  {
    $responses = Helper::$responses;
    
    $allPerm = Array( 'all' => HakAkses::all());
    $userPerm = Array('granted' => PermissionRepository::getPositionPerm($idJabatan));

    array_push($responses['data'], $allPerm);
    array_push($responses['data'], $userPerm);
    $responses['success'] = true;
    $responses['state_code'] = 200;

    return response()->json($responses, $responses['state_code']);
  }

  public function savePositionPermission(Request $request, $idJabatan = null)
  {
    $responses = Helper::$responses;
    $inputs = $request->all();
    $result = PermissionRepository::savePermission($responses, $idJabatan, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'UpdatePermissions', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
}
