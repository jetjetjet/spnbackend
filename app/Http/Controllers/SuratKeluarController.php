<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SuratKeluarRepository;
use App\Http\Repositories\DisSuratKeluarRepository;
use App\Http\Repositories\AuditTrailRepository;
use App\Http\Repositories\NotificationRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;

class SuratKeluarController extends Controller
{
  public function getAll(Request $request)
  {
    $user = request()->user();
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $isAdmin = $user->tokenCan('is_admin') ? true : false;
    $data = SuratKeluarRepository::getList($filter, Auth::user()->getAuthIdentifier(), $isAdmin);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $responses = Helper::$responses;
    $user = request()->user();
    $permissions = Array(
      'suratKeluar_approve' => $user->tokenCan('suratKeluar_approve') ? 1 : 0,
      'suratKeluar_agenda' => $user->tokenCan('suratKeluar_agenda') ? 1 : 0,
      'suratKeluar_sign' => $user->tokenCan('suratKeluar_sign') ? 1 : 0,
      'suratKeluar_verify' => $user->tokenCan('suratKeluar_verify') ? 1 :0
    );

    $result = SuratKeluarRepository::getById($responses, $id, $permissions);
    
    return response()->json($result, $result['state_code']);
  }

  //Save Surat Keluar
  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'jenis_surat' => 'required',
      'klasifikasi_id' => 'required',
      'sifat_surat' => 'required',
      'tujuan_surat' => 'required',
      'hal_surat' => 'required',
      'lampiran_surat' => 'required',
      'sign_user_id' => 'required',
      'approval_user_id' => 'required',
      'file' => 'required|file|max:5000|mimes:docx,doc',
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
    $result = SuratKeluarRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Save/Update Surat Keluar', Auth::user()->getAuthIdentifier());
    $result['data'] = [];
    return response()->json($result, $result['state_code']);
  }

  public function read(Request $request, $idDisposisi)
  {
    $read = DisSuratKeluarRepository::readDis($idDisposisi);
    $res = $read != null ? "Ok" : "Nok";
    return response()->json($res, 200);
  }

  //Agenda Surat untuk disetujui atasan
  public static function agendaSuratKeluar(Request $request, $id)
  {
    $respon = Helper::$responses;
    $rules = array(
      'file' => 'required|file|max:5000|mimes:pdf',
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
    $result = SuratKeluarRepository::agenda($respon, $id, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Agenda Surat Keluar', Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }

  public static function approveSuratKeluar(Request $request, $id)
  {
    $respon = Helper::$responses;
    $result = SuratKeluarRepository::approve($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Approve Surat Keluar', Auth::user()->getAuthIdentifier());

    $result['data'] = [];
    return response()->json($result, $result['state_code']);
  }

  public static function verifySuratKeluar(Request $request, $id)
  {
    $respon = Helper::$responses;
    $rules = array(
      'approved' => 'required',
      'to_user_id' => 'required',
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
    
    $inputs['log'] = json_decode($inputs['approved']) ? "VERIFIED" : "VERIFY_REJECTED";
    $result = SuratKeluarRepository::verify($respon, $id, $inputs, Auth::user()->getAuthIdentifier());

    $logTrail = Helper::convertLogForAuditTrail($inputs['log']);
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, $logTrail .' Surat Keluar', Auth::user()->getAuthIdentifier());

    $result['data'] = [];
    return response()->json($result, $result['state_code']);
  }

  public function delete(Request $request,$id)
  {
    $respon = Helper::$responses;
    $result = SuratKeluarRepository::delete($respon, $id, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Delete Surat Keluar', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function signSurat(Request $request, $id)
  {
    $respon = Helper::$responses;
    $inputs = $request->all();
    
    $inputs['log'] = json_decode($inputs['approved']) ? "SIGNED" : "SIGN_REJECTED";
    $inputs['approved'] = json_decode($inputs['approved']);
    $result = SuratKeluarRepository::signSurat($respon, $id, $inputs, Auth::user()->getAuthIdentifier());
    
    $logTrail = Helper::convertLogForAuditTrail($inputs['log']);
    $audit = AuditTrailRepository::saveAuditTrail($request, $result,  $logTrail .' Surat Keluar', Auth::user()->getAuthIdentifier());

    return response()->json($result, $result['state_code']);
  }

  public function gantiKata(Request $request)
  {
    $result = SuratKeluarRepository::replaceString();
    
    return response()->json($result, 200);
  }

  public function cetakNomor(Request $request, $id)
  {
    $respon = Helper::$responses;
    $rules = array(
      'tgl_agenda' => 'required',
      'tgl_teks' => 'required'
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

    $result = SuratKeluarRepository::generateNomorSurat($respon, $id, $inputs, 1);

    return response()->json($result, $result['state_code']);
  }
  
  public function generateNomorSurat(Request $request, $id)
  {
    $respon = Helper::$responses;
    $rules = array(
      'tgl_agenda' => 'required',
      'tgl_teks' => 'required'
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

    $result = SuratKeluarRepository::generateNomorSurat($respon, $id, $inputs, Auth::user()->getAuthIdentifier());
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Generate Nomor Surat', Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }
}
