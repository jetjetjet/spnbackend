<?php

namespace App\Http\Controllers;

use App\Http\Repositories\TemplateSuratRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;

class TemplateSuratController extends Controller
{
  public function getAll(Request $request)
	{
		$responses = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = TemplateSuratRepository::getList($filter);

    $responses['state_code'] = 200;
    $responses['success'] = true;
    $responses['data'] = $data;

    return response()->json($responses, 200);
  }
  
  public function getById(Request $request, $id = null)
  {
    $responses = Helper::$responses;
    $result = TemplateSuratRepository::getById($responses, $id);
    
    return response()->json($result, $result['state_code']);
  }

	public function save(Request $request, $id = null)
	{
		$respon = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'template_type' => 'required',
      'template_name' => 'required',
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
    
    $result = TemplateSuratRepository::save($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
	}

	public function delete($id)
	{
		$respon = Helper::$responses;
    $result = TemplateSuratRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
	}
}
