<?php

namespace app\Http\Repositories;

use App\Model\DisSuratKeluar;
use App\Http\Repositories\SuratKeluarRepository;
use App\Http\Repositories\NotificationRepository;
use App\Http\Repositories\ErrorLogRepository;
use App\Helpers\Helper;
use DB;
use Exception;

class DisSuratKeluarRepository
{

  public static function disSuratKeluar($respon, $inputs, $loginid)
  {
    try{
      DB::transaction(function () use (&$respon, $inputs, $loginid){
        if($inputs['file'] != "null"){
          $valid = SuratKeluarRepository::saveFile($respon, $inputs, $loginid);
          $inputs['file_id'] = isset($respon['file_id']) ? $respon['file_id'] : $inputs['file_id'];
          if (!$valid) return;
        } 
        
        if(json_decode($inputs['tujuan_user_id']) == null)
          $inputs['tujuan_user_id'] = self::getCreatedSurat($inputs['surat_keluar_id']);

        $valid = self::updateSuratKeluar($inputs, $loginid);
        if(!$valid) return;

        $valid = self::saveDisSuratKeluar($inputs, $loginid);
        if(!$valid) return;
        
        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = [];
        array_push($respon['messages'], trans('messages.successApprovedSK'));
      });
    } catch (\Exception $e) {
      $log =Array(
        'action' => 'DISSK',
        'modul' => 'SK',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? "NOT_RECORDED"
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.failApprovedSK'));
    }
    return $respon;
  }

  public static function updateSuratKeluar($inputs, $loginid)
  {
    $q = DB::table('surat_keluar')
      ->where('id', $inputs['surat_keluar_id'])
      ->where('active', '1')
      ->whereRaw("coalesce(is_verify,'0') = '0'")
      ->where('is_approve', '1')
      ->update([
        'is_verify' => $inputs['log'] == "APPROVED" ? '1' : '0',
        'surat_log' => $inputs['log'],
        'approved_at' => DB::raw('now()'),
        'approved_by' => $loginid
      ]);

    // if($inputs['log'] == "REJECTED")
    //   $inputs['tujuan_user_id'] = $q->created_by;

    if($q == null){
      throw new Exception('rollbacked');
      return false;
    } else {
      return true;
    }
  }

  public static function saveDisSuratKeluar($inputs, $loginid)
  {
    $ins = false;
    $q = DisSuratKeluar::create([
      'surat_keluar_id' => $inputs['surat_keluar_id'],
      'tujuan_user_id' => $inputs['tujuan_user_id'],
      'file_id' => $inputs['file_id'] ?? null,
      'keterangan' => $inputs['keterangan'],
      'log' => $inputs['log'],
      'logpos' => self::getLogPos($loginid),
      'is_read' => '0',
      'active' => '1',
      'created_at' => DB::raw('now()'),
      'created_by' => $loginid
    ]);

    if($q != null){
      $log = Helper::convertLogForNotif($inputs['log']);
      $sk = DB::table('surat_keluar')->where('active', '1')->where('id', $inputs['surat_keluar_id'])->select('tujuan_surat')->first();
      $notif = array(
        'id_reference' =>  $inputs['surat_keluar_id'],
        'id_subreference' => $q->id,
        'display' => 'Surat Keluar - ' . $sk->tujuan_surat . ' - ' . $log,
        'type' => 'SURATKELUAR'
      );
      
      $ins = true;
      $createNotif = NotificationRepository::createNotif($notif, $inputs['tujuan_user_id']);
    } else {
      throw new Exception('rollbacked');
    }
    return $ins;
  }

  public static function readDis($id)
  {
    $surat = DisSuratKeluar::where('id', $id)->first();

    if ($surat != null) {
      $surat->update([
        'is_read' => '1',
        'last_read' => DB::raw('now()')
      ]);
    }
    return $surat;
  }

  private static function getCreatedSurat($id)
  {
    $q = DB::table('surat_keluar')->where('active', '1')->where('id', $id)->select('created_by')->first();
    
    return $q->created_by;
  }

  private static function getLogPos($loginid)
  {
    $q = DB::table('gen_user as gu')->join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gu.id', $loginid)
      ->select('position_name')
      ->first();
    return $q->position_name;
  }
}