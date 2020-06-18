<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AuditTrailRepository;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
  public function getAll(Request $request)
  {
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = AuditTrailRepository::getAll($filter);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }
}
