<?php
namespace app\Http\Repositories;

use App\Model\DisSuratMasuk;
use App\Model\SuratMasuk;
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
        if($valid == null) return;

        $respon['success'] = true;
        $respon['state_code'] = 200;
        //$respon['data'] = $inputs;
        array_push($respon['messages'], trans('messages.successDispositionInMail'));
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function saveDisSuratMasuk($inputs, $loginid)
  {
    //$appr = $inputs['is_approved']  ?? "false";
    return DisSuratMasuk::create([
      'surat_masuk_id' => $inputs['surat_masuk_id'],
      'to_user_id' => $inputs['to_user_id'],
      'arahan' => $inputs['arahan'] ?? null,
      'is_tembusan' => $inputs['is_tembusan'] ?? null,
      'is_private' => $inputs['is_private'] ?? null,
      'log' => $inputs['log'],
      'is_read' => '0',
      'active' => '1',
      'created_at' => DB::raw('now()'),
      'created_by' => $loginid
    ]);
  }

  public static function readDis($id)
  {
    $surat = DisSuratMasuk::where('id', $id)->first();

    if ($surat != null) {
      $surat->update([
        'is_read' => '1',
        'last_read' => DB::raw('now()')
      ]);
    }
    return $surat;
  }
}