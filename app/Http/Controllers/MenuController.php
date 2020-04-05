<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HakAkses;
use App\Helpers\Helper;
use Auth;

class MenuController extends Controller
{
  public function getAllMenuPermission()
  {
    $perm = HakAkses::all();$dw = Auth::user()->getAuthIdentifier();
    dd($dw);
    return response()->json($perm, 200);
  }

  public function getList(Request $request)
  {
    $result = Helper::$responses;
  }

  public function getById(Request $request, $id = null)
  {

  }

  public function save(Request $request, $id = null)
  {

  }

  public function delete(Request $request, $id = null)
  {

  }
}
