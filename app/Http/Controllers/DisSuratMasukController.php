<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DisSuratMasukRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Http\Repositories\NotificationRepository;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Auth;
use Validator;

class DisSuratMasukController extends Controller
{
  public static function disposisiSuratMasuk(Request $request)
  {
    $respon = Helper::$responses;
    $rules = array(
      'surat_masuk_id' => 'required',
      'to_user_id' => 'required',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $respon['state_code'] = 400;
      $respon['messages'] = $validator->messages();
      $respon['data'] = $inputs;
      return response()->json($respon, 400);
    }

    $result = DisSuratMasukRepository::disSuratMasuk($respon, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Disposition', Auth::user()->getAuthIdentifier());
    //NOTIFICATION
    if($result['success'])
    {
      $dataNotif = Array(
        'type' => 'SURATMASUK',
        'to_user_id' => $result['data']['to_user_id'] ?? 0,
        'id' => $result['data']['surat_masuk_id'] ?? 0,
        'display' => 'Surat Masuk - Disposisi',
        'url' => '/incoming-mail-detail/' . $result['data']['surat_masuk_id']
      );
      
      $notif = NotificationRepository::save($dataNotif, Auth::user()->getAuthIdentifier());
    }
    unset($result['file_id'], $result['file']);
    $result['data'] = [];

    return response()->json($result, $result['state_code']);
  }
}

