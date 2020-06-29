<?php
namespace App\Http\Controllers;

use App\Http\Repositories\SuratMasukRepository;
use App\Http\Repositories\DisSuratMasukRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Http\Repositories\NotificationRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;

class SuratMasukController extends Controller
{
  public function getAll(Request $request)
  {
    $user = request()->user();
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $isAdmin = $user->tokenCan('is_admin') ? true : false;
    $data = SuratMasukRepository::getList($filter, Auth::user()->getAuthIdentifier(), $isAdmin);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $user = request()->user();
    $permissions = Array(
      'suratMasuk_close' => $user->tokenCan('suratMasuk_close') ? 1 : 0,
      'suratMasuk_disposition' => $user->tokenCan('suratMasuk_disposition') ? 1 : 0
    );
    $responses = Helper::$responses;
    $result = SuratMasukRepository::getById($responses, $id, $permissions);
    
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'asal_surat' => 'required',
      'nomor_surat' => 'required',
      'tgl_surat' => 'required',
      'to_user_id' => 'required',
      'sifat_surat' => 'required',
      'klasifikasi' => 'required',
      'file' => 'required|file|max:5000|mimes:pdf,docx,doc',
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

		// Validation fails?
		if ($validator->fails()){
			$result['state_code'] = 400;
      $result['messages'] = $validator->messages();
      $result['data'] = $inputs;
      return response()->json($result, 400);
    }
    
    $result = SuratMasukRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());

    //Notif
    if($result['success']){
      $dataNotif = Array(
        'type' => 'SURATMASUK',
        'to_user_id' => $inputs['to_user_id'],
        'id' => $result['id'] ?? 0,
        'display' => 'Surat Masuk - '. $inputs['asal_surat'],
        'url' => '/incoming-mail-detail/' . $result['id']
      );
      $notif = NotificationRepository::save($dataNotif, Auth::user()->getAuthIdentifier());
    }
    unset($result['id'], $result['file_id'], $inputs['file']);
    $result['data'] = [];    
    
    return response()->json($result, $result['state_code']);
  }

  public function read(Request $request, $idDisposisi)
  {
    $read = DisSuratMasukRepository::readDis($idDisposisi, Auth::user()->getAuthIdentifier());
    $res = $read != null ? "Ok" : "Nok";
    return response()->json($res, 200);
  }
  
  public static function tutupSuratMasuk(Request $request, $id)
  {
    $respon = Helper::$responses;
    $result = SuratMasukRepository::tutup($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Close', Auth::user()->getAuthIdentifier());

    //NOTIFICATION
    if($result['success']){
      $dataNotif = Array(
        'type' => 'SURATMASUK',
        'to_user_id' => $result['data']['created_by'] ?? 0,
        'id' => $result['id'] ?? 0,
        'display' => 'Surat Masuk Ditutup - '. $result['data']['asal_surat'],
        'url' => '/incoming-mail-detail/' . $result['data']['id']
      );
      $notif = NotificationRepository::save($dataNotif, Auth::user()->getAuthIdentifier());
    }
    $result['data'] = [];
    
    return response()->json($result, $result['state_code']);
  }

  public function delete(Request $request, $id)
  {
    $respon = Helper::$responses;
    $result = SuratMasukRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }
}
