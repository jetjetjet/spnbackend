<?php

namespace app\Http\Repositories;

use App\Model\SifatSurat;
use App\Http\Repositories\ErrorLogRepository;
use DB;
use Exception;

class SifatSuratRepository
{
  public static function getList($filter, $perm)
  {
    $q = SifatSurat::where('active', '1')
      ->select('id', 'sifat_surat', 
      DB::raw("
        case when 1 = ". $perm['sifatSurat_delete'] ." then 1 else 0 end as can_delete
      "))
      ->get();

    return $q;
  }

  public static function save($respon, $inputs, $loginid)
  {
    try{
      $save = SifatSurat::create([
        'sifat_surat' => $inputs['sifat_surat'],
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($respon['messages'], sprintf(trans('messages.succesSaveUpdate'), "Simpan ", $save->sifat_surat));
    } catch(\Exception $e){
      $log =Array(
        'action' => 'SAV',
        'modul' => 'SIFATSURAT',
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
      $sm = DB::table('surat_keluar')->where('active','1')->where('sifat_surat', $id)->first();
      $sk = DB::table('surat_masuk')->where('active','1')->where('sifat_surat', $id)->first();
      if($sk != null || $sm != null){
        array_push($respon['messages'], sprintf(trans('messages.errorDelReferenceUser'), 'Sifat Surat'));
        return $respon;
      }
      $sifatSurat = SifatSurat::where('active', '1')->where('id', $id)->firstOrFail();

      $sifatSurat->update([
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
        'modul' => 'SIFATSURAT',
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
    $q = SifatSurat::where('active','1')

      ->orderBy('sifat_surat', 'ASC')
      ->select('id', DB::raw("sifat_surat as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }
}