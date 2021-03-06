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
    $inputs = $request->all();
    $param = Array(
      'per_page' => $inputs['per_page'] ?? 10,
      'order' => $inputs['order'] ?? null,
      'filter' => $inputs['filter'] ?? null,
      'q' => $inputs['q'] ?? null
    );
    $isAdmin = $user->tokenCan('is_admin') ? true : false;
    $data = SuratMasukRepository::getList($param, Auth::user()->getAuthIdentifier(), $isAdmin);

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
    $result = SuratMasukRepository::getById($responses, $id, $permissions, Auth::user()->getAuthIdentifier());
    
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
      'perihal' => 'required',
      'tgl_surat' => 'required',
      'to_user_id' => 'required',
      'sifat_surat' => 'required',
      'klasifikasi_id' => 'required',
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
    
    $result = SuratMasukRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier(), Auth::user()->getAuthPosition());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update', Auth::user()->getAuthIdentifier());

    unset($result['id'], $result['file_id'], $inputs['file']);
    $result['data'] = [];    
    
    return response()->json($result, $result['state_code']);
  }

  public function read(Request $request, $idDisposisi)
  {
    $read = DisSuratMasukRepository::readDis($idDisposisi);
    $res = $read != null ? "Ok" : "Nok";
    return response()->json($res, 200);
  }
  
  public static function tutupSuratMasuk(Request $request, $id)
  {
    $respon = Helper::$responses;
    $result = SuratMasukRepository::tutup($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Close', Auth::user()->getAuthIdentifier());

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
