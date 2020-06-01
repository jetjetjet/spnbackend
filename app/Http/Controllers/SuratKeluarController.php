<?php

namespace App\Http\Controllers;

use App\Model\SuratKeluar;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

class SuratKeluarController extends Controller
{
   public function getAll()
   {
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = SuratKeluar::getSuratKeluar($filter);

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
   }

   public function getById(Request $request, $id = null)
   {
     
   }
}
