<?php

namespace app\Http\Repositories;

use App\Model\DisSuratKeluar;
use App\Http\Repositories\SuratKeluarRepository;
use App\Helpers\Helper;
use DB;
use Exception;

class DisSuratKeluarRepository
{

  public static function disSuratKeluar($respon, $inputs, $loginid)
  {
    $inputs['log'] = "disposition";
    try{
      DB::transaction(function () use (&$respon, $inputs, $loginid){
        if($inputs['file'] != "null"){
          $valid = SuratKeluarRepository::saveFile($respon, $inputs, $loginid);
          $inputs['file_id'] = isset($respon['file_id']) ? $respon['file_id'] : $inputs['file_id'];
          if (!$valid) return;
        } 
        
        if($inputs['tujuan_user'] == null)
          $inputs['tujuan_user'] = self::getCreatedSurat($inputs['surat_keluar_id']);

        $valid = self::updateSuratKeluar($inputs, $loginid);
        if($valid == null) return;

        $valid = self::saveDisSuratKeluar($inputs, $loginid);
        if($valid == null) return;
        
        $respon['notif'] = $valid->is_approved ? "Disetujui dan diteruskan untuk ttd" : "Ditolak dan dikembalikan untuk revisi";
        $respon['success'] = true;
        $respon['state_code'] = 200;
        $inputs['file_id'] = $inputs['file'] != "null" ? $respon['file_id'] : 0;
        $respon['data'] = $valid;
        array_push($respon['messages'], trans('messages.successDisposition'));
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function updateSuratKeluar($inputs, $loginid)
  {
    return DB::table('surat_keluar')
      ->where('id', $inputs['surat_keluar_id'])
      ->where('active', '1')
      ->where('is_agenda', '0')
      ->where('is_approved', '0')
      ->update([
        'is_disposition' => '1',
        'disposition_at' => DB::raw('now()'),
        'disposition_by' => $loginid
      ]);
  }

  public static function saveDisSuratKeluar($inputs, $loginid)
  {
    $appr = $inputs['is_approved']  ?? "false";
    return DisSuratKeluar::create([
      'surat_keluar_id' => $inputs['surat_keluar_id'],
      'tujuan_user' => $inputs['tujuan_user'],
      'file_id' => $inputs['file_id'] ?? null,
      'keterangan' => $inputs['keterangan'],
      'log' => $inputs['log'],
      'is_approved' => json_decode($appr),
      'approved_by' => $appr = "true" ? $loginid : null,
      'is_read' => '0',
      'active' => '1',
      'created_at' => DB::raw('now()'),
      'created_by' => $loginid
    ]);
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
}