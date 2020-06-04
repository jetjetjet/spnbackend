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
    try{
      DB::transaction(function () use (&$respon, $inputs, $loginid){
        $valid = SuratKeluarRepository::saveFile($respon, $inputs, $loginid);
        if (!$valid) return;

        $inputs['file_id'] = isset($respon['file_id']) ? $respon['file_id'] : $inputs['file_id'];

        $valid = self::saveDisSuratKeluar($inputs, $loginid);
        if($valid == null) return;

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $inputs['file_id'] = $respon['file_id'];
        unset($respon['file_id'], $inputs['file']);
        $respon['data'] = $inputs;
        array_push($respon['messages'], trans('messages.successDisposition'));
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function saveDisSuratKeluar($inputs, $loginid)
  {
    return DisSuratKeluar::create([
      'surat_keluar_id' => $inputs['surat_keluar_id'],
      'tujuan_user' => $inputs['tujuan_user'],
      'file_id' => $inputs['file_id'],
      'keterangan' => $inputs['keterangan'],
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
}