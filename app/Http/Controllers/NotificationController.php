<?php

namespace App\Http\Controllers;

use App\Http\Repositories\NotificationRepository;
use App\Http\Repositories\DisSuratKeluarRepository;
use App\Http\Repositories\DisSuratMasukRepository;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

class NotificationController extends Controller
{
  public function getCount()
  {
    $respon = Helper::$responses;
    $result = NotificationRepository::countNotif($respon, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }

  public function getNotif()
  {
    $respon = Helper::$responses;
    $result = NotificationRepository::getNotif($respon, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }

  public function getAll(Request $request)
  {
    $result = Helper::$responses;
    $filter = Helper::mapFilter($request);
    $data = NotificationRepository::getNotifList($filter, Auth::user()->getAuthIdentifier());

    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $data;

    return response()->json($result, 200);
  }

  public function read(Request $request, $id, $subid, $type){
    $notification = $request->user()->notifications()->where('id', $id)->first();
    if($notification) {
        $notification->markAsRead();
        if($type == "SURATKELUAR"){
          $read = DisSuratKeluarRepository::readDis($subid);
        } else if ($type == "SURATMASUK"){
          DisSuratMasukRepository::readDis($subid);
        } else {
          // lewat
        }
    }
    
    $respon = Helper::$responses;
    $result = NotificationRepository::countNotif($respon, Auth::user()->getAuthIdentifier());
    return response()->json($result, $result['state_code']);
  }
}