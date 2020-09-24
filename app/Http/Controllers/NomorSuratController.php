<?php

namespace App\Http\Controllers;

use App\Http\Repositories\NomorSuratRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;

class NomorSuratController extends Controller
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
    $data = NomorSuratRepository::getAll($param);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, $result['state_code']);
	}
}
