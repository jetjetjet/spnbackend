<?php

namespace App\Http\Controllers;

use App\Http\Repositories\TipeSuratRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;

class TipeSuratController extends Controller
{
	public function getAll(Request $request)
	{
		$responses = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $user = request()->user();
    $permissions = Array(
      'tipeSurat_delete' => $user->tokenCan('tipeSurat_delete') || $user->tokenCan('is_admin') ? 1 : 0
    );
    $data = TipeSuratRepository::getList($filter, $permissions);

    $responses['state_code'] = 200;
    $responses['success'] = true;
    $responses['data'] = $data;

    return response()->json($responses, 200);
  }

  public function save(Request $request, $id = null)
	{
		$respon = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'tipe_surat' => 'required'
		);
		
    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

		// Validation fails?
		if ($validator->fails()){
			$respon['state_code'] = 400;
      $results['messages'] = Array($validator->messages()->first());
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }
    
    $result = TipeSuratRepository::save($id, $respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
	}

	public function delete(Request $request,$id)
	{
		$respon = Helper::$responses;
    $result = TipeSuratRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
	}

	public function search(Request $request)
	{
		$respon = Helper::$responses;
		//$keyword = $request['keyword'];
		$result = TipeSuratRepository::search($respon);

		return response()->json($result, $result['state_code']);
  }
}
