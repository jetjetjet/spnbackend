<?php

namespace app\Http\Repositories;

use App\Model\GenNotif;
use DB;
use Exception;

class NotificationRepository
{
  public static function save($data, $loginid)
  {
    $result = false;
    $save = GenNotif::create([
      'type' => $data['type'],
      'to_user_id' => $data['to_user_id'],
      'reference_id' => $data['id'],
      'url' => $data['url'],
      'display' => $data['display'],
      'active' => '1',
      'created_at' => DB::raw("now()"),
      'created_by' => $loginid
    ]);

    if($save != null)
      $result = true;
    
    return $result;
  }

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
			$q = $q->orderBy('gn.id', 'DESC');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select(
      'gn.id',
      'gn.display',
      'gn.reference_id',
      'gn.type'
    )->get();

    return $data;
  }

  public static function countNotif($loginid)
  {
    $notif = self::selectNotif($loginid)->count();
    return $notif;
  }

  public static function getNotif($respon, $loginid)
  {
    $notif = self::selectNotif($loginid)
      ->select(
        'gn.id',
        'gn.display',
        'gn.reference_id',
        'gn.type'
      )->skip(0)->take(5)->get();
    $respon['data'] = $notif;
    $respon['success'] = true;
    $respon['state_code'] = 200;
    return $respon;
  }

  private static function selectNotif($loginid)
  {
    return db::table('gen_notif as gn')
    ->leftJoin('surat_keluar as sk', function($q){
      $q->on('gn.type', DB::raw("'SURATKELUAR'"))
        ->on('gn.reference_id', 'sk.id')
        ->on('sk.active', DB::raw("'1'"));})
    ->leftJoin('dis_surat_keluar as dsk', function($q){
      $q->on('dsk.surat_keluar_id', 'sk.id')
        ->on('dsk.tujuan_user', 'gn.to_user_id')
        ->on('dsk.is_read', DB::raw("null"))
        ->on('dsk.active',  DB::raw("'1'"));})    
    ->leftJoin('surat_masuk as sm', function($q){
      $q->on('gn.type', DB::raw("'SURATMASUK'"))
        ->on('gn.reference_id', 'sm.id')
        ->on('sm.active', DB::raw("'1'"));})
    ->leftJoin('dis_surat_masuk as dsm', function($q){
      $q->on('dsm.surat_masuk_id', 'sk.id')
        ->on('dsm.to_user_id', 'gn.to_user_id')
        ->on('dsk.is_read', DB::raw("null"))
        ->on('dsm.active',  DB::raw("'1'"));})
    ->where('gn.active', '1')
    ->where('gn.to_user_id', $loginid);
  }
}