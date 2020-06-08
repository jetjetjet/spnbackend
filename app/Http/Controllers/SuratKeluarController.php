<?php

namespace App\Http\Controllers;

use App\Http\Repositories\SuratKeluarRepository;
use App\Http\Repositories\DisSuratKeluarRepository;
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
    
  }

  public function save(Request $request, $id = null)
  {
    $results = Helper::$responses;
    $user = request()->user(); 

    // Validation rules.
    $rules = array(
      'jenis_surat' => 'required',
      'klasifikasi_surat' => 'required',
      'sifat_surat' => 'required',
      'tujuan_surat' => 'required',
      'hal_surat' => 'required',
      'lampiran_surat' => 'required',
      'approval_user' => 'required',
      'to_user' => 'required',
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
    
    $result = SuratKeluarRepository::save($id, $results, $inputs, Auth::user()->getAuthIdentifier());
    
    return response()->json($result, $result['state_code']);
  }

  public function read(Request $request, $idDisposisi)
  {
    $read = DisSuratKeluarRepository::readDis($idDisposisi);
    $res = $read != null ? "Ok" : "Nok";
    return response()->json($res, 200);
  }
}
