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
    $rules = array(
      'surat_keluar_id' => 'required',
      'tujuan_user' => 'required',
      'file' => 'mimes:pdf,docx,doc'
    );

    $inputs = $request->all();
    if(isset($inputs['file'])){

    }
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $respon['state_code'] = 400;
      $respon['messages'] = $validator->messages();
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }

    $result = DisSuratKeluarRepository::disSuratKeluar($respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Disposition', Auth::user()->getAuthIdentifier());
    
    return response()->json($result, $result['state_code']);
  }
}
