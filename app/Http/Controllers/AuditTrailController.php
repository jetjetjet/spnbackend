<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AuditTrailRepository;
use Illuminate\Http\Request;
use Auth;
use App\Helpers\Helper;

class AuditTrailController extends Controller
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
    $result['data'] = AuditTrailRepository::getAll($param);

    $result['state_code'] = 200;
    $result['success'] = true;

    return response()->json($result, $result['state_code']);
  }
}
