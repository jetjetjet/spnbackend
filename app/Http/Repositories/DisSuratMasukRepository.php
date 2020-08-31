<?php
namespace app\Http\Repositories;

use App\Model\DisSuratMasuk;
use App\Model\SuratMasuk;
use App\Http\Repositories\NotificationRepository;
use App\Helpers\Helper;
use DB;
use Exception;

class DisSuratMasukRepository
{

  public static function disSuratMasuk($respon, $inputs, $loginid)
  {
    try{
      $inputs['log'] = 'disposition';
      DB::transaction(function () use (&$respon, $inputs, $loginid){
        $valid = self::saveDisSuratMasuk($inputs, $loginid);
        if(!$valid) return;

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $valid;
        array_push($respon['messages'], trans('messages.successDispositionInMail'));
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorAdministrator'));
    }
    return $respon;
  }

  public static function saveDisSuratMasuk($inputs, $loginid)
  {
    //Mulai Transaction
    $result = false;
    DB::beginTransaction();
    try{
      //$appr = $inputs['is_approved']  ?? "false";
      foreach($inputs['to_user_id'] as $userid ){
        $q = DisSuratMasuk::create([
          'surat_masuk_id' => $inputs['surat_masuk_id'],
          'to_user_id' => $userid,
          'arahan' => $inputs['arahan'] ?? null,
          'is_tembusan' => $inputs['is_tembusan'] ?? null,
          'is_private' => $inputs['is_private'] ?? null,
          'log' => $inputs['log'],
          'is_read' => '0',
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);

        if($q != null){
          $notif = array(
            'id_reference' => $inputs['surat_masuk_id'],
            'display' => 'Surat Masuk - ' . ($inputs['nomor_surat'] ?? "_"),
            'type' => 'SURATMASUK'
          );
          $createNotif = NotificationRepository::createNotif($notif, $userid);
        }
      }

      DB::commit();
      $result = true;
    }catch(\Exception $e){
      dd($e);
      DB::rollback();
    }
    return $result;
  }

  public static function readDis($id, $loginid)
  {
    $surat = DisSuratMasuk::where('id', $id)->where('to_user_id', $loginid)->where('active', '1')->first();

    if ($surat != null) {
      $surat->update([
        'is_read' => '1',
        'last_read' => DB::raw('now()')
      ]);
    }
    return $surat;
  }
}