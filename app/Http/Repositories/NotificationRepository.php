<?php

namespace app\Http\Repositories;

use App\Notifications\NotifCount;
use App\Model\GenNotif;
use App\User;
use DB;
use Exception;

class NotificationRepository
{
  public static function getNotifList($filter, $loginid)
  {
    $data = new \stdClass();
    $q = self::selectNotif($loginid);

    if($filter->search){
			foreach($filter->search as $qCol){
				$sCol = explode('|', $qCol);
				$fCol = str_replace('"', '', $sCol[0]);
				$q = $q->where($sCol[0], 'like', '%'.$sCol[1].'%');
			}
    }
    
    $qCount = $q->count();

    if ($filter->sortColumns){
			$order = $filter->sortColumns[0];
			$q = $q->orderBy($order->column, $order->order);
		} else {
			$q = $q->orderBy('created_at', 'ASC');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select(
      'id',
      'data'
    )->get();

    return $data;
  }

  public static function createNotif($data, $toUserId)
  {
    $user = User::where('active', '1')->where('id', $toUserId)->first();
    $notif = DB::table('notifications')->where('notifiable_id', $toUserId)->whereNull('read_at')->count();
    if($user != null){
      $notif = Array(
        'id_reference' => $data['id_reference'],
        'display' => $data['display'],
        'full_name' =>$user->full_name,
        'type' => $data['type'],
        'totalCount' => $notif + 1
      );
  
      $user->notify(new NotifCount($notif));
    }
    return true;
  }

  public static function countNotif($respon, $loginid)
  {
    $notif = self::selectNotif($loginid)->count();
    $respon['data'] = $notif;
    $respon['success'] = true;
    $respon['state_code'] = 200;
    return $respon;
  }

  public static function getNotif($respon, $loginid)
  {
    $notif = self::selectNotif($loginid)
      ->select(
        'id',
        'data'
      )->skip(0)->take(5)->get();
    $respon['data'] = $notif;
    $respon['success'] = true;
    $respon['state_code'] = 200;
    return $respon;
  }

  private static function selectNotif($loginid)
  {
    return DB::table('notifications')
      ->where('notifiable_id', $loginid)
      ->whereNull('read_at')
      ->orderBy('created_at', 'DESC');
  }
}