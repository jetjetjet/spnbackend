<?php

namespace app\Http\Repositories;

use App\Model\TipeSurat;
use App\Http\Repositories\ErrorLogRepository;
use DB;
use Exception;

class TipeSuratRepository
{
  public static function getList($filter, $perm)
  {
    $q = TipeSurat::where('active', '1')
      ->select('id', 'tipe_surat', 
      DB::raw("
        case when 1 = ". $perm['tipeSurat_delete'] ." then 1 else 0 end as can_delete
      "))
      ->get();

    return $q;
  }

  public static function save($respon, $inputs, $loginid)
  {
    try{
      $save = TipeSurat::create([
        'tipe_surat' => $inputs['tipe_surat'],
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $save;
      array_push($respon['messages'], sprintf(trans('messages.succesSaveUpdate'), "Simpan", $inputs['tipe_surat']));
    } catch(\Exception $e){
      $log =Array(
        'action' => 'SAV',
        'modul' => 'TIPESURAT',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? "NOT_RECORDED"
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $sm = DB::table('surat_keluar')->where('active','1')->where('jenis_surat', $id)->first();
      $sk = DB::table('surat_masuk')->where('active','1')->where('jenis_surat', $id)->first();
      if($sk != null || $sm != null){
        array_push($respon['messages'], sprintf(trans('messages.errorDelReferenceUser'), 'Tipe Surat'));
        return $respon;
      }
      $tipeSurat = TipeSurat::where('active', '1')->where('jenis_surat', $id)->firstOrFail();

      $tipeSurat->update([
        'active' => '0',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($respon['messages'], sprintf(trans('messages.successDeleting'), 'Sifat Surat'));
    } catch (\Exception $e) {
      $log =Array(
        'action' => 'DEL',
        'modul' => 'TIPESURAT',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? "NOT_RECORDED"
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function search($respon)
  {
    $q = TipeSurat::where('active','1')

      ->orderBy('tipe_surat', 'ASC')
      ->select('id', DB::raw("tipe_surat as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }
}