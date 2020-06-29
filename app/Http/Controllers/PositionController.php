<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PositionRepository;
use App\Http\Repositories\AuditTrailRepository;
use Illuminate\Http\Request;
use App\HakAkses;
use DB;
use Auth;
use Validator;
use App\Helpers\Helper;

class PositionController extends Controller
{
	public function getAll(Request $request)
	{
		$responses = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = PositionRepository::getList($filter);

    $responses['state_code'] = 200;
    $responses['success'] = true;
    $responses['data'] = $data;

    return response()->json($responses, 200);
	}

	public function getById(Request $request, $id = null)
  {
    $responses = Helper::$responses;
    $result = PositionRepository::getById($responses, $id);
    
    return response()->json($result, $result['state_code']);
  }

	public function save(Request $request, $id = null)
	{
		$respon = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'group_id' => 'required',
      'position_name' => 'required',
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
    
    $result = PositionRepository::save($respon, $id, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());
    
    return response()->json($result, $result['state_code']);
	}

	public function delete($id)
	{
		$respon = Helper::$responses;
    $result = PositionRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
	}

	public function searchPosition(Request $request)
	{
		$respon = Helper::$responses;
		//$keyword = $request['keyword'];
		$result = PositionRepository::searchPosition($respon);

		return response()->json($result, $result['state_code']);
  }
  
  public function getAllPermissions()
  {
    $hakAkses = HakAkses::All();
    $result = Helper::$responses;
    $result['success'] = true;
    $result['state_code'] = 200;
    array_push($result['data'], $hakAkses);

		return response()->json($result, $result['state_code']);
  }
  
  public function getPositionMenu(Request $request, $idPosisi = null)
  {
    $respon = Helper::$responses;
    $result = PositionRepository::getPositionMenuById($respon, $idPosisi);

		return response()->json($result, $result['state_code']);
  }

  public function searchParentPosition(Request $request)
  {
    
		$respon = Helper::$responses;
		$result = PositionRepository::searchParentPosition($respon);

		return response()->json($result, $result['state_code']);
  }
}
