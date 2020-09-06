<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DisSuratKeluarRepository;
use App\Http\Repositories\AuditTrailRepository;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Auth;
use Validator;

class DisSuratKeluarController extends Controller
{
  public static function disposisiSuratKeluar(Request $request)
  {
    $respon = Helper::$responses;
    $inputs = $request->all();
    
    $rules = array(
      'surat_keluar_id' => 'required',
      'tujuan_user_id' => 'required'
    );

    if($inputs['file'] != "null"){
      $rules['file'] = 'mimes:docx,doc';
    }

    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $respon['state_code'] = 400;
      $respon['messages'] = $validator->messages();
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }

    $inputs['log'] = json_decode($inputs['approved']) ? "APPROVED" : "REJECTED";
    $result = DisSuratKeluarRepository::disSuratKeluar($respon, $inputs, Auth::user()->getAuthIdentifier());
    $logTrail = Helper::convertLogForAuditTrail($inputs['log']);
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, $logTrail .' Surat Keluar', Auth::user()->getAuthIdentifier());

    $result['data'] = [];

    return response()->json($result, $result['state_code']);
  }
}
