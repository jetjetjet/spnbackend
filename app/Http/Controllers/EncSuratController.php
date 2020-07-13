<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EncSuratRepository;
use Illuminate\Http\Request;
use DB;
use Auth;
use Validator;
use App\Helpers\Helper;

class EncSuratController extends Controller
{
  public function cekKode(Request $request)
  {
    $respon = Helper::$responses;
    // Validation rules.
    $rules = array(
      'key' => 'required',
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
    
    $result = EncSuratRepository::validasi($respon, $inputs);
    
    return response()->json($result, $result['state_code']);
  }
}
