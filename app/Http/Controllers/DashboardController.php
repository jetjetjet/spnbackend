<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DashboardRepository;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Auth;

class DashboardController extends Controller
{
  public function getTugas(Request $request)
  {
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = DashboardRepository::getTugas($filter, Auth::user()->getAuthIdentifier());

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }
}
