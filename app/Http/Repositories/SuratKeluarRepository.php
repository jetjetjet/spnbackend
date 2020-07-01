<?php

namespace app\Http\Repositories;

use App\Model\SuratKeluar;
use App\Model\DisSuratKeluar;
use App\Http\Repositories\DisSuratKeluarRepository;
use App\Model\File;
use App\Helpers\Helper;
use DB;
use Exception;

class SuratKeluarRepository
{

  public static function getList($filter, $loginid, $isAdmin)
  {
    $data = new \StdClass();
    $q = DB::table('surat_keluar as sk')
      ->join('dis_surat_keluar as dsk',function($query){
        $query->on('dsk.surat_keluar_id', 'sk.id')
        ->on('dsk.active', DB::raw("'1'"));
      })
      ->leftJoin('gen_user as cr', 'cr.id', 'dsk.created_by')
      ->leftJoin('gen_position as gp', 'gp.id', 'cr.position_id')
      ->leftJoin('gen_group as gg', 'gg.id', 'gp.group_id')
      ->where('sk.active', '1')
      ->where('dsk.active', '1');
      
    if(!$isAdmin)
      $q = $q->where('dsk.tujuan_user', $loginid)
        ->orWhere('dsk.created_by', $loginid);
    
    if($filter->search){
      foreach($filter->search as $qCol){
        $sCol = explode('|', $qCol);
        $fCol = str_replace('"', '', $sCol[0]);
        $q = $q->where($sCol[0], 'like', '%'.$sCol[1].'%');
      }
    }
      
    $qCount = $q->distinct('sk.id')->count();
  
    if ($filter->sortColumns){
      $order = $filter->sortColumns[0];
      $q = $q->orderBy($order->column, $order->order);
    } else {
      $q = $q->orderBy('sk.id', 'DESC');
    }
      
    $q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
      
    $data->totalCount = $qCount;
    $data->data =  $q->select(
      'sk.id',
      'dsk.id as disposisi_id',
      'tujuan_surat',
      'jenis_surat',
      'hal_surat',
      'group_name',
      'position_name',
      DB::raw("coalesce(nomor_agenda, 'belum diisi') as nomor_agenda"),
      DB::raw("coalesce(nomor_surat, 'belum diisi') as nomor_surat"),
      DB::raw("coalesce(tgl_surat::varchar, 'belum diisi') as tgl_surat"),
      DB::raw("case when dsk.is_approved = '1' then 'Disetujui'
        when dsk.is_approved = '0' then 'Ditolak'
        else 'Draft' end as status"),
      'sk.is_approved',
      DB::raw("
        case when sk.is_approved = '0' and sk.created_by = ". $loginid ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when sk.is_approved = '0' and sk.created_by = ". $loginid ." then 1 else 0 end as can_delete
      ")
    )->distinct('sk.id')->get();

    return $data;
  }

  public static function checkSuratKeluar($id)
  {
    return SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->select(
        'approval_user',
        'tujuan_surat',
        'to_user',
        'created_by'
      )
      ->first();
  }

  public static function getById($respon, $id, $perms)
  {
    $header = DB::table('surat_keluar as sk')
      ->join('gen_user as cr', 'cr.id', 'sk.created_by')
      ->join('gen_klasifikasi_surat as gks', 'klasifikasi_id', 'gks.id')
      ->join('gen_user as appr', 'appr.id', 'approval_user')
      ->leftJoin('gen_position as gp', 'gp.id', 'appr.position_id')
      ->join('gen_user as tu', 'tu.id', 'to_user')
      ->leftJoin('gen_user as md', 'md.id', 'sk.modified_by')
      ->where('sk.active', '1')
      ->where('sk.id', $id)
      ->select(
        DB::raw("coalesce(nomor_agenda, 'belum diisi') as nomor_agenda"),
        DB::raw("coalesce(nomor_surat, 'belum diisi') as nomor_surat"),
        DB::raw("coalesce(tgl_surat::varchar, 'belum diisi') as tgl_surat"),
        DB::raw("coalesce(file_id::varchar, 'belum diisi') as file_id"),
        'jenis_surat',
        'sifat_surat',
        'tujuan_surat',
        'hal_surat',
        'lampiran_surat',
        'approval_user',
        'appr.full_name as approval_name',
        'klasifikasi_id',
        'gks.nama_klasifikasi as klasifikasi_name',
        'to_user',
        'tu.full_name as to_username',
        'gp.position_name',
        'cr.full_name as created_by',
        'sk.created_at',
        'md.full_name as modified_by',
        'sk.modified_by',
        DB::raw($perms['suratKeluar_approve'] . "as can_approve"),
        DB::raw($perms['suratKeluar_disposition'] . "as can_disposition"),
        DB::raw($perms['suratKeluar_agenda'] . "as can_agenda")
      )->first();
    
    if($header != null){
      $data = new \stdClass();

      $disposisi = DB::table('dis_surat_keluar as dsk')
        ->join('gen_user as to', 'dsk.created_by', 'to.id')
        ->leftJoin('gen_position as gp', 'to.position_id', 'gp.id')
        ->leftJoin('gen_file as f', 'file_id', 'f.id')
        ->where('dsk.active', '1')
        ->where('dsk.surat_keluar_id', $id)
        ->select('dsk.id', 
          DB::raw("case when log = 'create' then 'Surat dibuat oleh: '
            when log = 'disposition' then 'Surat Didisposisikan oleh: '
            when log = 'finish' then 'Surat selesai'
            else '' end as label_disposisi"),
          'surat_keluar_id', 
          'dsk.created_by',
          'to.full_name as created_by',
          'gp.position_name',
          'file_id',
          'is_read',
          'last_read',
          'dsk.keterangan',
          'original_name as file_name',
          'file_path',
          'dsk.created_at'
        )->get();

        $data = $header;
        $data->disposisi = $disposisi;

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $data;
    } else {
      array_push($respon['messages'], trans('messages.errorNotFound'));
      $respon['state_code'] = 400;
    }
    return $respon;
  }

  public static function save($id, $result,$inputs, $loginid)
  {
    try{
      DB::transaction(function () use (&$result, $id, $inputs, $loginid){
        $valid = self::saveFile($result, $inputs, $loginid);
        if (!$valid) return;

        $valid = self::saveSuratKeluar($result, $id, $inputs, $loginid);
        if (!$valid) return;

        $result['success'] = true;
        $result['state_code'] = 200;
        $inputs['file_id'] = $result['file_id'];
        $inputs['id'] = $result['id'];
        array_push($result['messages'], trans('messages.successSaveSuratKeluar'));
       // $result['data'] = $inputs;
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $result['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
    }
    return $result;
  }

  public static function saveFile(&$result, $inputs, $loginid)
  {
    $file = Helper::prepareFile($inputs, '/upload/suratkeluar');
    if ($file){
      $newFile = File::create([
        'file_name' => $file->newName,
        'file_path' => '/upload/suratkeluar/'.$file->newName,
        'original_name' => $file->originalName,
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      $result['file_id'] = $newFile->id;
    } 
    return true;
  }

  private static function saveSuratKeluar(&$result, $id, $inputs, $loginid)
  {
    $inputs['file_id'] = isset($result['file_id']) ? $result['file_id'] : $inputs['file_id'];
    if ($id){
      $tSurat = SuratKeluar::where('active', 1)->where('id', $id)->first();
      if ($tSurat == null || $tsurat->created_by != $loginid){
        array_push($result['messages'], trans('messages.errorNotFoundInvalid'));
        return false;
      } else {
        $update = $tSurat->update([
          'nomor_agenda' => $inputs['nomor_agenda'] ?? null,
          'nomor_surat' => $inputs['nomor_surat'] ?? null,
          'tgl_surat' => $inputs['tgl_surat'] ?? null,
          'jenis_surat' => $inputs['jenis_surat'],
          'klasifikasi_id' => $inputs['klasifikasi_id'],
          'sifat_surat' => $inputs['sifat_surat'],
          'tujuan_surat' => $inputs['tujuan_surat'],
          'hal_surat' => $inputs['hal_surat'],
          'lampiran_surat' => $inputs['lampiran_surat'],
          'approval_user' => $inputs['approval_user'],
          //'file_id' => $inputs['file_id'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
        
        $result['id'] = $update->id ?: $id;
        return true;
      }
    } else {
      $insert = SuratKeluar::create([
        'nomor_agenda' => $inputs['nomor_agenda'] ?? null,
        'nomor_surat' => $inputs['nomor_surat'] ?? null,
        'tgl_surat' => $inputs['tgl_surat'] ?? null,
        'klasifikasi_id' => $inputs['klasifikasi_id'],
        'jenis_surat' => $inputs['jenis_surat'],
        'sifat_surat' => $inputs['sifat_surat'],
        'tujuan_surat' => $inputs['tujuan_surat'],
        'to_user' => $inputs['to_user'],
        'hal_surat' => $inputs['hal_surat'],
        'lampiran_surat' => $inputs['lampiran_surat'],
        'approval_user' => $inputs['approval_user'],
        'is_approved' => '0',
        //'file_id' => $inputs['file_id'],
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      
      if($insert != null){
        $dataDis = Array(
          'surat_keluar_id' => $insert['id'],
          'tujuan_user' => $inputs['to_user'],
          'file_id' => $inputs['file_id'],
          'log' => "create",
          'keterangan' => null,
        );
        $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
        if($dis == null){
          throw new Exception('rollbacked');
        }
      }
      $result['id'] = $insert->id ?: $id;
      return true;
    }
  }

  public static function agenda($respon, $id, $inputs, $loginid)
  {
    $sm = SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->where('is_approved', '0')
      ->first();
    
    if($sm != null){
      try{
        DB::transaction(function () use (&$respon, $sm, $inputs, $loginid){
          $valid = self::saveFile($respon, $inputs, $loginid);
          if (!$valid) return;

          $valid = self::updateAgenda($respon, $sm, $inputs, $loginid);
          if (!$valid) return;
          
          unset($respon['file_id']);
          $respon['data'] = $sm;
          $respon['success'] = true;
          $respon['state_code'] = 200;
          array_push($respon['messages'], trans('messages.successUpdatedAgenda'));
        });
      } catch (\Exception $e) {
        if ($e->getMessage() === 'rollbacked') return $result;
        $result['state_code'] = 500;
        array_push($result['messages'], $e->getMessage());
      }
    } else {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.suratAlreadyApproved'));
    }
    return $respon;
  }

  private static function updateAgenda(&$respon, $sm, $inputs, $loginid)
  {
    $tes = $sm->update([
      'nomor_agenda' => $inputs['nomor_agenda'],
      'nomor_surat' => $inputs['nomor_surat'],
      'tgl_surat' => $inputs['tgl_surat'],
      'file_id' => $respon['file_id'],
      'modified_at' => DB::raw("now()"),
      'modified_by' => $loginid
    ]);
    return $tes;
  }

  public static function approve($respon, $id, $loginid)
  {
    $sk = SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->where('is_approved', '0')
      ->first();
    
    if($sk != null){
      $sk->update([
        'is_approved' => '1',
        'approved_at' => DB::raw("now()"),
        'approved_by' => $loginid,
        'modified_at' => DB::raw("now()"),
        'modified_by' => $loginid,
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $sk;
      array_push($respon['messages'], trans('messages.successApprovedSuratKeluar'));
    } else {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.suratAlreadyApproved'));
    }
    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    $cekDisposisi = DisSuratKeluar::where('active', '1')
      ->where('surat_keluar_id', $id)
      ->count();
    if($cekDisposisi > 1)
    {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorDeleteSuratKeluarDispositionAlr'));
    } else {
      $header = SuratKeluar::where('active', '1')
        ->where('id', $id)
        ->update([
          'active' => '0',
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
      $detail = DisSuratKeluar::where('active', '1')
      ->where('surat_keluar_id', $id)
      ->update([
        'active' => '0',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);

      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($respon['messages'], trans('messages.successDeleteSuratKeluar'));
    }
    return $respon;
  }
}