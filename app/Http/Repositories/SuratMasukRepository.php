<?php

namespace app\Http\Repositories;

use App\Model\SuratMasuk;
use App\Model\DisSuratMasuk;
use App\Http\Repositories\DisSuratMasukRepository;
use App\Http\Repositories\ErrorLogRepository;
use App\Model\File;
use App\Helpers\Helper;
use DB;
use Exception;

class SuratMasukRepository
{

  public static function getList($param, $loginid, $isAdmin)
  {
    $sQ = DB::table('dis_surat_masuk')
      ->where('active', '1');
    
    if(!$isAdmin)
      $sQ = $sQ->whereRaw('(to_user_id = ? or created_by = ? )', Array($loginid, $loginid));
    
    $sQ = $sQ->distinct('surat_masuk_id');

    $q = DB::table('surat_masuk as sm')
    ->joinSub($sQ, 'dsm',function($query){
      $query->on('dsm.surat_masuk_id', 'sm.id');
    })
    ->leftJoin('gen_user as cr', 'cr.id', 'dsm.created_by')
    ->leftJoin('gen_position as gp', 'gp.id', 'cr.position_id')
    ->leftJoin('gen_group as gg', 'gg.id', 'gp.group_id')
    ->where('sm.active', '1');

    $q =  $q->select(
      'sm.id',
      'dsm.id as disposisi_id',
      'asal_surat',
      'nomor_surat',
      'tgl_surat',
      'tgl_diterima',
      'perihal',
      DB::raw("case when is_read = '1' then 'Dibaca' else 'Belum Dibaca' end as status_read "),
      'cr.full_name as user_created_by',
      'gp.position_name as user_created_position',
      'gg.group_name as user_created_group',
      'is_closed',
      DB::raw("
        case when is_closed = '0' and sm.created_by = ". $loginid ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when is_closed = '0' and sm.created_by = ". $loginid ." then 1 else 0 end as can_delete
      "));
      
    $q = $param['order'] != null
      ? $q->orderByRaw("sm.". $param['order'])
      : $q->orderBy('sm.id', 'DESC');

    $q = $param['filter'] != null 
      ? $q->whereRaw("UPPER(sm.".$param['filter']. ") like UPPER(?) ", ['%' . trim($param['q']) . '%' ])
      : $q;

    $data = $q->paginate($param['per_page']);;

    return $data;
  }

  public static function getById($respon, $id, $perms, $loginid)
  {
    $dispSub = DB::table('dis_surat_masuk as dsm')
      // ->join('gen_user as dp', 'dp.id', 'dsm.to_user_id')
      // ->join('gen_position as gp', 'gp.id', 'dp.position_id')
      ->leftJoin('gen_user as dc', 'dc.id', 'dsm.created_by')
      ->leftJoin('gen_position as gpc', 'gpc.id', 'dc.position_id')
      ->leftJoin('gen_user as tuj', 'tuj.id', 'dsm.to_user_id')
      ->leftJoin('gen_position as ptuj', 'ptuj.id', 'tuj.position_id')
      ->where('dsm.active','1')
      ->where('dsm.log', 'DISPOSITION')
      ->where('surat_masuk_id', $id)
      ->where('to_user_id', $loginid)
      ->orderBy('dsm.created_at', 'DESC')
      ->select('surat_masuk_id', 'arahan', 'gpc.position_name', 'dc.full_name', 'ptuj.parent_id as l_position_parent_id', 'tuj.id as l_id', 'dsm.created_at');

    $header = DB::table('surat_masuk as sm')
      ->join('gen_user as cr', 'cr.id', 'sm.created_by')
      ->join('gen_position as gp', 'gp.id', 'cr.position_id')
      ->join('gen_user as tuj', 'tuj.id', 'sm.to_user_id')
      ->join('gen_position as ptuj', 'ptuj.id', 'tuj.position_id')
      ->join('gen_group as gg', 'gg.id', 'gp.group_id')
      ->join('gen_klasifikasi_surat as gks', 'klasifikasi_id', 'gks.id')
      ->leftJoin('gen_file as gf', 'sm.file_id', 'gf.id')
      ->leftJoin('gen_file as df', 'sm.disposisi_file_id', 'df.id')
      ->leftJoinSub($dispSub, 'disp', function ($join) {
        $join->on('sm.id', 'disp.surat_masuk_id');
      })
      //->join('gen_user as cr', 'cr.id', 'sm.created_by')
      ->where('sm.active', '1')
      ->where('sm.id', $id)
      ->select(
        'sm.id',
        'asal_surat',
        'cr.full_name as created_by',
        'gp.position_name',
        'gg.group_name',
        'file_id',
        'gf.file_path as file_path',
        'gf.original_name as file_name',
        'df.file_path as disposition_file_path',
        'df.original_name as disposition_file_name',
        'to_user_id',
        'tuj.full_name as to_user_name',
        'ptuj.position_name as to_position_name',
        //DB::raw("tuj.full_name || ' - ' || coalesce(ptuj.position_name,'') as to_user_name"),
        'perihal',
        'nomor_surat',
        DB::raw("to_char(tgl_surat, 'yyyy-mm-dd') as tgl_surat"),
        DB::raw("to_char(tgl_diterima, 'yyyy-mm-dd') as tgl_diterima"),
        'lampiran',
        'sifat_surat',
        'jenis_surat',
        'klasifikasi_id',
        'gks.nama_klasifikasi as klasifikasi_name',
        'keterangan',
        'prioritas',
        'is_closed',
        'closed_at',
        //Disposition if any
        'disp.position_name as disposition_position_name',
        'disp.arahan as disposition_arahan',
        'disp.full_name as disposition_name',
        'disp.created_at as disposition_created_at',
        DB::raw("case when disp.l_position_parent_id is not null and disp.l_id =" . $loginid . " then 1 else 0 end as disposition_kasi"),
        
        //permissions
        DB::raw("case when sm.is_closed = '0' and 1 =" . $perms['suratMasuk_close'] . " then 1 else 0 end as can_closed"),
        DB::raw("case when sm.is_closed = '0' and 1 =" . $perms['suratMasuk_disposition'] . " then 1 else 0 end as can_disposition")
      )
      ->first();
    
    if($header != null){
      $data = new \stdClass();
      
      $detail = DB::table('dis_surat_masuk as dsm')
        ->join('gen_user as cr', 'dsm.created_by', 'cr.id')
        ->leftJoin('gen_position as pcr', 'cr.position_id', 'pcr.id')
        ->join('gen_user as dp', 'dsm.to_user_id', 'dp.id')
        ->where('dsm.active', '1')
        ->where('dsm.surat_masuk_id', $id)
        ->orderBy('dsm.created_at', 'ASC')
        ->select(
          'dsm.id as disposisi_id',
          DB::raw("case when log = 'CREATED' then 'Dibuat oleh: ' || cr.full_name || ' - ' || pcr.position_name
            when log = 'DISPOSITION' then 'Diteruskan kepada: ' || dp.full_name || ' - ' || logpos
            when log = 'CLOSED' then 'Surat diselesaikan oleh: ' || cr.full_name || ' - ' || logpos
            else '' end as label_history"),
          DB::raw("case when is_read = '1' then 'Dibaca' else 'Belum Dibaca' end as status_read "),
          'logpos as position_name',
          'arahan',
          'last_read'
        )->get();

        $data = $header;
        $data->disposisi = $detail;

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $data;
    } else {
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Surat Masuk'));
      $respon['state_code'] = 400;
    }
    return $respon;
  }

  public static function save($id, $result,$inputs, $loginid, $positionid)
  {
    try{
      DB::transaction(function () use (&$result, $id, $inputs, $loginid, $positionid){
        $valid = self::saveFile($result, $inputs, $loginid);
        if (!$valid) return;

        $valid = self::saveSuratMasuk($result, $id, $inputs, $loginid, $positionid);
        if (!$valid) return;

        $result['success'] = true;
        $result['state_code'] = 200;
        $inputs['file_id'] = $result['file_id'];
        $inputs['id'] = $result['id'];
        array_push($result['messages'], trans('messages.successSaveSuratMasuk'));
        //$result['data'] = $inputs;
      });
    } catch (\Exception $e) {
      $log =Array(
        'action' => 'SAV',
        'modul' => 'SM',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $result['state_code'] = 400;
      array_push($result['messages'], trans('messages.errorSaveSM'));
    }
    return $result;
  }

  public static function saveFile(&$result, $inputs, $loginid)
  {
    $file = Helper::prepareFile($inputs, '/upload/suratmasuk');
    if ($file){
      $newFile = File::create([
        'file_name' => $file->newName,
        'file_path' => '/upload/suratmasuk/'.$file->newName,
        'original_name' => $file->originalName,
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      $result['file_id'] = $newFile->id;
    } 
    return true;
  }

  private static function saveSuratMasuk(&$result, $id, $inputs, $loginid, $positionid)
  {
    $inputs['file_id'] = isset($result['file_id']) ? $result['file_id'] : $inputs['file_id'];
    if ($id){
      $tSurat = SuratMasuk::where('active', 1)->where('id', $id)->first();
      if ($tSurat == null || $tSurat->created_by != $loginid){
        array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Surat Masuk'));
        return false;
      } else {
        $update = $tSurat->update([
          'asal_surat' => $inputs['asal_surat'] ?? null,
          'perihal' => $inputs['perihal'] ?? null,
          'nomor_surat' => $inputs['nomor_surat'] ?? null,
          'tgl_surat' => $inputs['tgl_surat'] ?? null,
          'lampiran' => $inputs['lampiran'] ?? null,
          'sifat_surat' => $inputs['sifat_surat'] ?? null,
          'jenis_surat' => $inputs['jenis_surat'] ?? null,
          'klasifikasi_id' => $inputs['klasifikasi_id'] ?? null,
          'prioritas' => $inputs['prioritas'] ?? null,
          'keterangan' => $inputs['keterangan'] ?? null,
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
        
        $result['id'] = $tSurat->id ?: $id;
        return true;
      }
    } else {
      $insert = SuratMasuk::create([
        'file_id' => $inputs['file_id'],
        'asal_surat' => $inputs['asal_surat'] ?? null,
        'perihal' => $inputs['perihal'] ?? null,
        'nomor_surat' => $inputs['nomor_surat'] ?? null,
        'tgl_surat' => $inputs['tgl_surat'] ?? null,
        'to_user_id' => $inputs['to_user_id'],
        'tgl_diterima' => DB::raw('now()'),
        'lampiran' => $inputs['lampiran'] ?? null,
        'sifat_surat' => $inputs['sifat_surat'] ?? null,
        'jenis_surat' => $inputs['jenis_surat'] ?? null,
        'klasifikasi_id' => $inputs['klasifikasi_id'] ?? null,
        'prioritas' => $inputs['prioritas'] ?? null,
        'keterangan' => $inputs['keterangan'] ?? null,
        'is_closed' => '0',
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      
      if($insert != null){
        $dataDis = Array(
          'surat_masuk_id' => $insert['id'],
          'to_user_id' => Array($inputs['to_user_id']),
          'arahan' => $inputs['keterangan'],
          'log' => 'CREATED',
          'nomor_surat' => $inputs['nomor_surat'],
          'is_tembusan' => null,
          'is_private' => null
        );
        $dis = DisSuratMasukRepository::saveDisSuratMasuk($result, $dataDis, $loginid, $positionid);
        if($dis == null){
          throw new Exception();
        }
      }
      $result['id'] = $insert->id ?: $id;
      return true;
    }
  }

  public static function tutup($respon, $id, $loginid)
  {
    $sm = SuratMasuk::where('active', '1')
      ->where('id', $id)
      ->where('is_closed', '0')
      ->first();
    
    if($sm != null){
      $sm->update([
        'is_closed' => '1',
        'closed_at' => DB::raw("now()"),
        'closed_by' => $loginid,
        'modified_at' => DB::raw("now()"),
        'modified_by' => $loginid,
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $sm;
      array_push($respon['messages'], trans('messages.successClosedSuratMasuk'));
    } else {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.suratAlreadyClosed'));
    }
    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    $cekDisposisi = DisSuratMasuk::where('active', '1')
      ->where('surat_masuk_id', $id)
      ->count();
    if($cekDisposisi > 1)
    {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorDeleteSuratMasukDispositionAlr'));
    } else {
      $header = SuratMasuk::where('active', '1')
        ->where('id', $id)
        ->update([
          'active' => '0',
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
      $detail = DisSuratMasuk::where('active', '1')
      ->where('surat_masuk_id', $id)
      ->update([
        'active' => '0',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);

      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($respon['messages'], sprintf(trans('messages.successDeleting'), 'Surat Masuk'));
    }
    return $respon;
  }
}