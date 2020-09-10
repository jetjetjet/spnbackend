<?php

namespace app\Http\Repositories;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Str;
use App\Http\Repositories\DisSuratKeluarRepository;
use App\Model\File;
use App\Helpers\Helper;

use App\Model\SuratKeluar;
use App\Model\DisSuratKeluar;
use App\Model\EncSurat;
use App\Model\NomorSurat;
use Carbon\Carbon;

use DB;
use Exception;
use PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class SuratKeluarRepository
{

  public static function getList($filter, $loginid, $isAdmin)
  {
    $data = new \StdClass();

    $q = DB::table('surat_keluar as sk')
      ->join('dis_surat_keluar as dsk', 'dsk.surat_keluar_id', 'sk.id')
      ->leftJoin('gen_user as cr', 'cr.id', 'sk.created_by')
      ->leftJoin('gen_position as pcr', 'cr.position_id', 'pcr.id')

      ->leftJoin('gen_user as app', 'app.id', 'sk.approved_by')
      ->leftJoin('gen_position as papp', 'app.position_id', 'papp.id')

      ->leftJoin('gen_user as ver', 'ver.id', 'sk.verified_by')
      ->leftJoin('gen_position as pver', 'ver.position_id', 'pver.id')

      ->leftJoin('gen_user as ag', 'ag.id', 'sk.agenda_by')
      ->leftJoin('gen_position as pag', 'ag.position_id', 'pag.id')

      ->leftJoin('gen_user as sign', 'sign.id', 'sk.signed_by')
      ->leftJoin('gen_position as psign', 'sign.position_id', 'psign.id')
      //->join('gen_group as gg', 'gg.id', 'gp.group_id')
      ->where('sk.active', '1');
    if(!$isAdmin)
      $q = $q->where('dsk.tujuan_user_id', $loginid)
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
      DB::raw("coalesce(nomor_agenda, 'belum diisi') as nomor_agenda"),
      DB::raw("coalesce(nomor_surat, 'belum diisi') as nomor_surat"),
      DB::raw("coalesce(to_char(tgl_surat, 'dd-mm-yyyy'), 'belum diisi') as tgl_surat"),
      DB::raw("case when is_approve = '1' and surat_log = 'CREATED' then 'Konsep - '|| cr.full_name
        when is_approve = '1' and surat_log = 'REVISED' then 'Revisi - '|| cr.full_name
        when is_approve = '1' and coalesce(is_verify,'0') = '0' and surat_log = 'REJECTED' then 'Ditolak - ' ||  app.full_name
        when is_approve = '1' and is_verify = '1' and surat_log = 'APPROVED' then 'Disetujui - ' ||  app.full_name
        when is_approve = '1' and is_verify = '0' and surat_log = 'VERIFY_REJECTED' then 'Ditolak - ' ||  ver.full_name
        when is_verify = '1' and is_agenda = '1' and surat_log = 'VERIFIED' then 'Diverifikasi - ' || ver.full_name
        when is_agenda = '1' and is_sign = '1' and surat_log = 'AGENDA' then 'Diagenda - ' ||  ag.full_name
        when is_agenda = '1' and is_sign = '1' and surat_log = 'SIGNED' then 'Ditandatangani - ' || sign.full_name
        when is_approve = '1' and is_verify = '0' and is_sign = '0' and surat_log = 'SIGN_REJECTED' then 'Ditolak - ' || sign.full_name
        else '' end as status_surat
      "),
      DB::raw("case when is_approve = '1' and (surat_log = 'CREATED' or surat_log = 'REVISED') then pcr.position_name
      when is_approve = '1' and is_verify = '0' and surat_log = 'REJECT' then papp.position_name
      when is_approve = '1' and is_verify = '1' and surat_log = 'APPROVED' then papp.position_name
      when is_approve = '1' and is_verify = '0' and surat_log = 'VERIFY_REJECTED' then pver.position_name
      when is_verify = '1' and is_agenda = '1' and surat_log = 'VERIFIED' then pver.position_name
      when is_agenda = '1' and is_sign = '1' and surat_log = 'AGENDA' then pag.position_name
      when is_agenda = '1' and is_sign = '1' and surat_log = 'SIGNED' then psign.position_name
      when is_approve = '1' and is_verify = '0' and is_sign = '0' and surat_log = 'SIGN_REJECTED' then psign.position_name
        else '' end as status_position_name
      "),
      DB::raw(" cr.full_name ||to_char(sk.created_at, 'dd-mm-yyyy') as created_by"),
      DB::raw("
        case when coalesce(sk.is_verify,'0') = '0' and sk.created_by = ". $loginid ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when coalesce(sk.is_verify,'0') = '0' and sk.created_by = ". $loginid ." then 1 else 0 end as can_delete
      ")
    )->distinct('sk.id')->get();

    return $data;
  }

  public static function checkSuratKeluar($id)
  {
    return SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->select(
        'sign_user_id',
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
      ->join('gen_user as appr', 'appr.id', 'sk.approval_user_id')
      ->leftJoin('gen_position as pappr', 'pappr.id', 'appr.position_id')
      ->join('gen_user as sign', 'sign.id', 'sk.sign_user_id')
      ->leftJoin('gen_file as gf', 'gf.id', 'sk.file_id')
      ->leftJoin('gen_file as agf', 'agf.id', 'sk.agenda_file_id')
      ->where('sk.active', '1')
      ->where('sk.id', $id)
      ->select(
        DB::raw("coalesce(nomor_agenda, 'belum diisi') as nomor_agenda"),
        DB::raw("coalesce(nomor_surat, 'belum diisi') as nomor_surat"),
        DB::raw("coalesce(to_char(tgl_surat, 'yyyy-mm-dd'), 'belum diisi') as tgl_surat"),
        DB::raw("coalesce(file_id::varchar, 'belum diisi') as signed_file_id"),
        'gf.file_name as signed_file_name',
        'gf.file_path as signed_file_path',
        'agf.file_path as agenda_file_path',
        'agf.file_name as agenda_file_name',
        'is_agenda',
        'jenis_surat',
        'sifat_surat',
        'tujuan_surat',
        'hal_surat',
        'lampiran_surat',
        'approval_user_id',
        'appr.full_name as approval_name',
        'sign.full_name as sign_name',
        'klasifikasi_id',
        'gks.nama_klasifikasi as klasifikasi_name',
        'sign_user_id',
        'sign.full_name as to_username',
        'pappr.position_name',
        'cr.full_name as created_by',
        'sk.created_at',
        // 'md.full_name as modified_by',
        // 'sk.modified_by',
        DB::raw("case when sk.is_approve = '1' and (surat_log = 'VERIFY_REJECTED' or surat_log = 'CREATED' or surat_log = 'REVISED' or surat_log = 'SIGN_REJECTED') and 1 =" . $perms['suratKeluar_approve'] . " then 1 else 0 end as can_approve"),
        DB::raw("case when sk.is_approve = '1' and (surat_log = 'REJECTED') and 1 =" . $perms['suratKeluar_save'] . " then 1 else 0 end as can_edit"),
        DB::raw("case when sk.is_verify = '1' and (surat_log = 'APPROVED' or surat_log = 'SIGN_REJECTED') and 1 =" . $perms['suratKeluar_verify'] . " then 1 else 0 end as can_verify"),
        DB::raw("case when sk.is_agenda = '1' and surat_log = 'VERIFIED' and 1 =" . $perms['suratKeluar_agenda'] . " then 1 else 0 end as can_agenda"),
        DB::raw("case when sk.is_sign = '1' and surat_log = 'AGENDA' and 1 =" . $perms['suratKeluar_sign'] . " then 1 else 0 end as can_sign")
      )->first();
    
    if($header != null){
      $data = new \stdClass();

      $disposisi = DB::table('dis_surat_keluar as dsk')
        ->join('gen_user as cr', 'dsk.created_by', 'cr.id')
        ->leftJoin('gen_position as gp', 'cr.position_id', 'gp.id')
        ->leftJoin('gen_file as f', 'file_id', 'f.id')
        ->where('dsk.active', '1')
        ->where('dsk.surat_keluar_id', $id)
        ->orderBy('dsk.created_at', 'ASC')
        ->select('dsk.id', 
          DB::raw("case when log = 'CREATED' then 'Surat dibuat oleh: ' || cr.full_name
            when log = 'REVISED' then 'Surat direvisi oleh: ' || cr.full_name
            when log = 'REJECTED' or log = 'SIGN_REJECTED' then 'Surat ditolak oleh: ' || cr.full_name
            when log = 'APPROVED' then 'Surat disetujui oleh: ' || cr.full_name
            when log = 'VERIFIED' then 'Surat diverifikasi oleh: ' || cr.full_name
            when log = 'VERIFY_REJECTED' then 'Verifikasi ditolak oleh: ' || cr.full_name
            when log = 'AGENDA' then 'Surat diagenda oleh: ' || cr.full_name
            when log = 'SIGNED' then 'Surat ditandatangani oleh: ' || cr.full_name
            else '' end as label"),
          'surat_keluar_id', 
          'cr.full_name as created_by',
          'gp.position_name',
          'file_id',
          DB::raw("case when is_read = '1' then 'Dibaca' else 'Belum Dibaca' end as status_read "),
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
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Surat Keluar'));
      $respon['state_code'] = 400;
    }
    return $respon;
  }

  public static function save($id, $result,$inputs, $loginid)
  {
    try{
      DB::transaction(function () use (&$result, $id, $inputs, $loginid){
        
        if($inputs['pail']){
          $valid = self::saveFile($result, $inputs, $loginid);
          if (!$valid) return;
        } else {
          $inputs['file_id'] = null;
        }

        $valid = self::saveSuratKeluar($result, $id, $inputs, $loginid);
        if (!$valid) return;

        $result['success'] = true;
        $result['state_code'] = 200;
        $inputs['id'] = $result['id'];
        array_push($result['messages'], trans('messages.successSaveSuratKeluar'));
       // $result['data'] = $inputs;
      });
    } catch (\Exception $e) {
      if ($e->getMessage() === 'rollbacked') return $result;
      $result['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorSaveSK'));
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
    } else {
      return false;
    }
    return true;
  }

  private static function saveSuratKeluar(&$result, $id, $inputs, $loginid)
  {
    $inputs['file_id'] = isset($result['file_id']) ? $result['file_id'] : $inputs['file_id'];
    if ($id){
      $tSurat = SuratKeluar::where('active', 1)->where('id', $id)->first();
      if ($tSurat == null || $tSurat->created_by != $loginid){
        array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Surat Keluar'));
        return false;
      } else {
        $tempLog = $tSurat->surat_log;
        $update = $tSurat->update([
          'jenis_surat' => $inputs['jenis_surat'],
          'klasifikasi_id' => $inputs['klasifikasi_id'],
          'sifat_surat' => $inputs['sifat_surat'],
          'tujuan_surat' => $inputs['tujuan_surat'],
          'hal_surat' => $inputs['hal_surat'],
          'lampiran_surat' => $inputs['lampiran_surat'],
          'approval_user_id' => $inputs['approval_user_id'],
          'sign_user_id' => $inputs['sign_user_id'],
          'surat_log' => 'REVISED',
          'is_approve' => '1',
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        if($tempLog == "REJECTED"){
          $dataDis = Array(
            'surat_keluar_id' => $tSurat->id,
            'tujuan_user_id' => $inputs['approval_user_id'],
            'file_id' => $inputs['file_id'],
            'log' => "REVISED",
            //'tujuan_surat' => $inputs['tujuan_surat'],
            'keterangan' => 'Revisi',
          );
          $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
          if(!$dis){
            throw new Exception('rollbacked');
          }
        }
        
        $result['id'] = $tSurat->id ?: $id;
        return true;
      }
    } else {
      $insert = SuratKeluar::create([
        'klasifikasi_id' => $inputs['klasifikasi_id'],
        'jenis_surat' => $inputs['jenis_surat'],
        'sifat_surat' => $inputs['sifat_surat'],
        'tujuan_surat' => $inputs['tujuan_surat'],
        'sign_user_id' => $inputs['sign_user_id'],
        'hal_surat' => $inputs['hal_surat'],
        'lampiran_surat' => $inputs['lampiran_surat'],
        'approval_user_id' => $inputs['approval_user_id'],
        'is_approve' => '1',
        'is_verify' => '0',
        'is_agenda' => '0',
        'is_sign' => '0',
        'surat_log' => 'CREATED',
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      
      if($insert != null){
        $dataDis = Array(
          'surat_keluar_id' => $insert['id'],
          'tujuan_user_id' => $inputs['approval_user_id'],
          'file_id' => $inputs['file_id'],
          'log' => "CREATED",
          //'tujuan_surat' => $inputs['tujuan_surat'],
          'keterangan' => 'Draft',
        );
        $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
        if(!$dis){
          throw new Exception('rollbacked');
        }
      }
      $result['id'] = $insert->id ?: $id;
      return true;
    }
  }

  public static function verify($respon, $id, $inputs, $loginid)
  {
    $sk = SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->where('is_approve', '1')
      ->where('is_verify', '1')
      ->where('is_agenda', '0')
      ->first();
    
    if($sk != null){
      DB::beginTransaction();
      try{
        $sk->update([
          'is_agenda' => '1',
          'surat_log' => $inputs['log'],
          'verified_at' => DB::raw("now()"),
          'verified_by' => $loginid,
          'modified_at' => DB::raw("now()"),
          'modified_by' => $loginid,
        ]);

        $dataDis = Array(
          'surat_keluar_id' => $sk->id,
          'tujuan_user_id' => $inputs['to_user_id'],
          'log' => $inputs['log'],
          'keterangan' => $inputs['keterangan']
        );

        $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
        if(!$dis){
          throw new Exception();
        }
        
        DB::commit();
        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successVerifySK'));
      } catch(\Exception $e){
        DB::rollback();
        $respon['success'] = false;
        $respon['state_code'] = 500;
        array_push($respon['messages'], trans('messages.errorVerifySK'));
      }
    } else {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.suratAlreadySigned'));
    }
    return $respon;
  }

  public static function agenda($respon, $id, $inputs, $loginid)
  {
    $sm = SuratKeluar::where('active', '1')
      ->where('id', $id)
      ->where('is_agenda', '1')
      ->where('is_verify', '1')
      ->where('is_sign', '0')
      ->first();
    
    if($sm != null){
      try{
        DB::transaction(function () use (&$respon, $sm, $inputs, $loginid){
          $valid = self::saveFile($respon, $inputs, $loginid);
          if (!$valid) return;

          $valid = self::updateAgenda($respon, $sm, $inputs, $loginid);
          if (!$valid) return;
          
          unset($respon['file_id']);
          $respon['success'] = true;
          $respon['state_code'] = 200;
          array_push($respon['messages'], trans('messages.successAgendaSK'));
        });
      } catch (\Exception $e) {
        if ($e->getMessage() === 'rollbacked') return $result;
        $result['state_code'] = 500;
        array_push($respon['messages'], trans('messages.errorAgendaSK'));
      }
    } else {
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.suratAlreadySigned'));
    }
    return $respon;
  }

  private static function updateAgenda(&$respon, $sm, $inputs, $loginid)
  {
    $tes = $sm->update([
      'modified_at' => DB::raw("now()"),
      'modified_by' => $loginid,
      'agenda_at' => DB::raw("now()"),
      'agenda_by' => $loginid,
      'surat_log' => 'AGENDA',
      'is_sign' => '1'
    ]);

    $dataDis = Array(
      'surat_keluar_id' => $sm->id,
      'tujuan_user_id' => $sm->sign_user_id,
      'file_id' => $respon['file_id'],
      'log' => "AGENDA",
      'keterangan' => "" // $inputs['keterangan'],
    );
    $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
    if(!$dis){
      throw new Exception('rollbacked');
    }
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
      array_push($respon['messages'], trans('messages.successApprovedSK'));
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
      array_push($respon['messages'], trans('messages.errorDeleteSKApproved'));
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
      array_push($respon['messages'], sprintf(trans('messages.successDeleting', 'Surat Keluar')));
    }
    return $respon;
  }

  public static function signSurat($respon, $id, $inputs, $loginid)
  {
    $sk = SuratKeluar::where('id', $id)
      ->where('active', '1')
      ->where('is_sign', '1')
      ->whereNull('signed_at')->first(); 
    if ($sk != null){
      if($inputs['approved']) {
        $isiKode = Str::random(12);
        $data = DB::table('dis_surat_keluar as dsk')
          ->join('gen_file as gf', 'gf.id', 'dsk.file_id')
          ->where('dsk.active', '1')
          ->where('surat_keluar_id', $id)
          ->where('log', 'AGENDA')
          ->orderBy('dsk.created_at', 'desc')
          ->select('file_path', 'file_name', 'original_name')
          ->first();

        if($data->file_path != null){
          $user = DB::table('gen_user as gu')
          ->where('active', '1')
          ->where('id', $loginid)
          ->select('ttd', 'email', 'username', 'full_name')
          ->first();

          $pdf = new Fpdi();
          $pdf->setPrintHeader(false);
          $pdf->setPrintFooter(false);
          $pdf->AddPage();
          // set the source file
          $pageCount = $pdf->setSourceFile(base_path() . $data->file_path);
          for($pageNo = 1; $pageNo <= $pageCount; $pageNo++){
            $tplId = $pdf->importPage(1);
            $pdf->useTemplate($tplId);
  
            if($pageNo == 1){
              //set certificate file
              $certificate = 'file://'. realpath('../stack/certificates/public/'. $user->username .'.crt');
              $private_key = 'file://'. realpath('../stack/certificates/private/'. $user->username .'.key');
  
              $info = array(
                'Name' => $user->full_name,
                'Location' => 'Kerinci',
                'Keterangan' => $inputs['keterangan'],
                'Email' => $user->email
              );
  
              $pdf->setSignature($certificate, $private_key, '', '', 2, $info);
              $pdf->setSignatureAppearance(170, 260, 15, 15);
            }
  
            $style = array(
              'border' => 1,
              'vpadding' => 'auto',
              'hpadding' => 'auto',
              'fgcolor' => array(0,0,0),
              'bgcolor' => false, //array(255,255,255)
              'module_width' => 1, // width of a single module in points
              'module_height' => 1 // height of a single module in points
            );
  
            // QRCODE,L : QR-CODE Low error correction
            $pdf->write2DBarcode($isiKode, 'QRCODE,L', 170, 260, 15, 15, $style, 'N');
          }
  
          $signed = time()."_signed_". $data->original_name;
          $signedPath = '/upload/suratkeluar/'. $signed;
          $pdf->Output(base_path() . $signedPath, 'F');
  
          $newFile = File::create([
            'file_name' => $signed,
            'file_path' => $signedPath,
            'original_name' => $signed,
            'active' => '1',
            'created_at' => DB::raw('now()'),
            'created_by' => $loginid
          ]);
  
          $encSurat = EncSurat::create([
            'key' => $isiKode,
            'surat_keluar_id' => $sk->id,
            'active' => '1',
            'created_at' => DB::raw('now()'),
            'created_by' => $loginid
          ]);
  
          $update = $sk->update([
            'file_id' => $newFile->id,
            'signed_by' => $loginid,
            'is_sign' => '0',
            'signed_at' => DB::raw('now()'),
            'status_log' => $inputs['log'],
            'modified_at' => DB::raw('now()'),
            'modified_by' => $loginid
          ]);
  
          $dataDis = Array(
            'surat_keluar_id' => $id,
            'tujuan_user_id' => $sk->created_by,
            'file_id' => $newFile->id,
            'log' => $inputs['log'],
            'keterangan' => $inputs['keterangan'],
          );
          $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
          //$pdf->reset();
          $respon['success'] = true;
          $respon['state_code'] = 200;
          array_push($respon['messages'], trans('messages.successSignSuratKeluar'));
        }
      } else {
        $update = $sk->update([
          //'file_id' => $newFile->id,
          'is_sign' => '0',
          'is_agenda' => '0',
          'is_verify' => '0',
          'is_approve' => '1',
          'surat_log' => $inputs['log'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        $dataDis = Array(
          'surat_keluar_id' => $id,
          'tujuan_user_id' => $sk->approved_by,
          'file_id' => null,
          'log' => $inputs['log'],
          'keterangan' => $inputs['keterangan'],
        );
        $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
        //$pdf->reset();
        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successRejectedSignSK'));
      }
    } else {
      array_push($respon['messages'], trans('messages.suratAlreadySigned'));
    }
    return $respon;
  }

  public static function generateNomorSurat($respon, $id, $inputs, $loginid)
  {
    $kKlasifikasi = DB::table('surat_keluar as sk')
      ->join('gen_klasifikasi_surat as gks', 'sk.klasifikasi_id', 'gks.id')
      ->join('gen_user as gu', 'gu.id', 'sk.created_by')
      ->join('gen_position as gp', 'gp.id', 'gu.position_id')
      ->join('gen_group as gg', 'gg.id', 'gp.group_id')
      ->where('sk.id', $id)
      ->where('sk.active', '1')
      ->select('gks.kode_klasifikasi', 'gks.nama_klasifikasi', 'gks.id as klasifikasi_id', 'gg.group_code')
      ->first();
    $getFile = DB::table("dis_surat_keluar as dsk")
      ->join('gen_file as gf', 'gf.id', 'dsk.file_id')
      ->where('dsk.active', '1')
      ->where('log', DB::raw("'VERIFIED'"))
      ->where('surat_keluar_id', $id)
      ->select('file_path', 'file_name', 'original_name', 'log')
      ->first();
    
    if($getFile == null){
      $getFile = DB::table("dis_surat_keluar as dsk")
      ->join('gen_file as gf', 'gf.id', 'dsk.file_id')
      ->where('dsk.active', '1')
      ->where('surat_keluar_id', $id)
      ->orderBy('dsk.created_at', 'DESC')
      ->select('file_path', 'file_name', 'original_name', 'log')
      ->first();
    }
      
    if($kKlasifikasi && $getFile){
      $getNomor = DB::table('gen_nomor_surat_keluar as gns')
        ->where('klasifikasi_id', $kKlasifikasi->klasifikasi_id)
        ->where('prefix', $kKlasifikasi->kode_klasifikasi)
        ->where('active', '1')
        ->select(DB::raw('coalesce(urut_surat,0) + 1 as nomor'), DB::raw('coalesce(urut_agenda,0) + 1 as agenda'))
        ->first();

      $latNo = $getNomor ? $getNomor->nomor : 1;
      $latAg = $getNomor ? $getNomor->agenda : 1;
      $GroupCode = $kKlasifikasi->kode_klasifikasi == "Surat Perintah Tugas" ? "SPPD" : $kKlasifikasi->group_code;
      
      $tahun = Carbon::now();
      $noSurat = $kKlasifikasi->kode_klasifikasi . "/" .  $latNo . "/" . $GroupCode ."/PDK/". $tahun->format('Y');
      $noAgenda = $kKlasifikasi->kode_klasifikasi . "/" .  $latAg . "/" . $GroupCode ."/PDK/". $tahun->format('Y');

      //Mulai Transaction
      DB::beginTransaction();
      //Replace Nomor Surat
      try{
        $path = base_path();
        $newFile = time()."_". $getFile->original_name .'_agenda';
        $newFilePath = '/upload/suratkeluar/' . $newFile.'.docx';
        
        $docx = new \PhpOffice\PhpWord\TemplateProcessor($path . $getFile->file_path);
        //$docx->setValue(array('{NOMOR_SURAT}' => $noSurat, '{TANGGAL_SURAT}' => $inputs['tgl_teks']));
            
        $docx->setValue('{NOMOR_SURAT}', $noSurat);
        $docx->setValue('{TGL_SURAT}', $inputs['tgl_teks']);
        $docx->saveAs( $path . $newFilePath, TRUE);

        $saveFileToDb = File::create([
          'file_name' => $newFile.'.docx',
          'file_path' => $newFilePath,
          'original_name' => $newFile,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);

        $saveNomor = NomorSurat::create([
          'periode' => $tahun->format('Y'),
          'prefix'=> $kKlasifikasi->kode_klasifikasi,
          'urut_surat' => $latNo,
          'urut_agenda' => $latAg,
          'no_surat' => $noSurat,
          'no_agenda' => $noSurat,
          'surat_keluar_id' => $id,
          'klasifikasi_id' => $kKlasifikasi->klasifikasi_id,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);

        $updateSK = SuratKeluar::where('id', $id)
          ->where('active', '1')
          ->where('is_agenda', '1')
          ->update([
            'nomor_surat' => $noSurat,
            'nomor_agenda' => $noAgenda,
            'tgl_surat' => $inputs['tgl_agenda'],
            'agenda_file_id' => $saveFileToDb->id,
            'modified_at' => DB::raw('now()'),
            'modified_by' => $loginid
          ]);
        
        DB::commit();

        // if($dis == null){
        //   throw new Exception('rollbacked');
        // }
        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.succeedGenerateNomorSurat'));

      } catch(\Exception $e){
        // lewat
        DB::rollback();
        $respon['success'] = false;
        $respon['state_code'] = 500;
        array_push($respon['messages'], trans('messages.errorGenerateSK'));
      }
    } else {
      $respon['success'] = false;
      $respon['state_code'] = 500;
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Surat Keluar'));
    }
    return $respon;
  }
}