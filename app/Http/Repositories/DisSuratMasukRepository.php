<?php
namespace app\Http\Repositories;

use App\Model\DisSuratMasuk;
use App\Model\SuratMasuk;
use App\Model\File;
use App\Http\Repositories\NotificationRepository;
use App\Http\Repositories\ErrorLogRepository;
use App\Helpers\Helper;
use DB;
use Exception;
use NcJoes\OfficeConverter\OfficeConverter;

class DisSuratMasukRepository
{

  public static function disSuratMasuk($respon, $inputs, $loginid, $positionid)
  {
    try{
      $inputs['log'] = 'DISPOSITION';
      $childKabid = self::cekKabid($positionid);
      DB::transaction(function () use (&$respon, $inputs, $loginid, $positionid, $childKabid){
        $valid = self::saveDisSuratMasuk($inputs, $loginid, $positionid);
        if(!$valid) return;
        if($childKabid > 1){
          $valid = self::generateLembarDisposisi($inputs, $loginid);
          if(!$valid) return;
        }

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $valid;
        array_push($respon['messages'], trans('messages.successDispositionInMail'));
      });
    } catch (\Exception $e) {
      $log =Array(
        'action' => 'DISSM',
        'modul' => 'DISSURATMASUK',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.failDispositionInMail'));
    }
    return $respon;
  }

  public static function generateLembarDisposisi($inputs, $loginid)
  {
    $update = false;
    $sM = SuratMasuk::where('surat_masuk.active', '1')
      ->where('surat_masuk.id', $inputs['surat_masuk_id']);
    //Mulai Transaction
    try{
      $updateSM = $sM->update([
        'kabid_id' => $loginid,
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
  
      $getSM = $sM->join('gen_user as kabid', 'surat_masuk.kabid_id' ,'kabid.id')
        ->join('gen_position as pkabid', 'pkabid.id', 'kabid.position_id')
        ->join('gen_user as sekr', 'sekr.id', 'surat_masuk.sekretaris_id')
        ->join('gen_position as psekr', 'psekr.id', 'sekr.position_id')
        ->join('gen_user as kadin', 'kadin.id', 'surat_masuk.kadin_id')
        ->join('gen_position as pkadin', 'pkadin.id', 'kadin.position_id')
        ->join('dis_surat_masuk as kabiddsm', 'kabiddsm.created_by', 'surat_masuk.kabid_id')
        ->join('dis_surat_masuk as kadindsm', 'kadindsm.created_by', 'surat_masuk.kadin_id')
        ->join('dis_surat_masuk as sekredsm', 'sekredsm.created_by', 'surat_masuk.sekretaris_id')
        ->select(
          'asal_surat',
          'perihal',
          'nomor_surat',
          DB::raw("to_char(tgl_surat, 'dd-mm-yyyy') as tgl_surat"),
          DB::raw("kabid.full_name || ' - ' || pkabid.position_name || ':' as label_kabid"),
          DB::raw("kabiddsm.arahan as arahan_kabid"),
          DB::raw("kadin.full_name || ' - ' || pkadin.position_name || ':' as label_kadin"),
          DB::raw("kadindsm.arahan as arahan_kadin"),
          DB::raw("sekr.full_name || ' - ' || psekr.position_name || ':' as label_sekre"),
          DB::raw("sekredsm.arahan as arahan_sekre")
          )->first();
      if($getSM != null){
        // $path = base_path();
        $path = '/home/admin/web/apisurat.disdikkerinci.id/public_html';
        $newFile = time()."_LembarDisposisi_". $getSM->asal_surat;
        $newFilePath = '/upload/suratmasuk/' . $newFile.'.docx';
        
        $docx = new \PhpOffice\PhpWord\TemplateProcessor($path . "/upload/suratmasuk/TEMPLATEDISPOSISI.docx");
        //$docx->setValue(array('{NOMOR_SURAT}' => $noSurat, '{TANGGAL_SURAT}' => $inputs['tgl_teks']));
            
        $docx->setValue('{NOMOR_SURAT}', $getSM->nomor_surat);
        $docx->setValue('{TGL_SURAT}', $getSM->tgl_surat);
        $docx->setValue('{PERIHAL}', $getSM->perihal);
        $docx->setValue('{ASAL_SURAT}', $getSM->asal_surat);
        $docx->setValue('{LBL_SEKRE}', $getSM->label_sekre);
        $docx->setValue('{DISPOSISI_SEKRE}', $getSM->arahan_sekre);
        $docx->setValue('{LBL_KADIN}', $getSM->label_kadin);
        $docx->setValue('{DISPOSISI_KADIN}', $getSM->arahan_kadin);
        $docx->setValue('{LBL_KABID}', $getSM->label_kabid);
        $docx->setValue('{DISPOSISI_KABID}', $getSM->arahan_kabid);
        $docx->saveAs( $path . $newFilePath, TRUE);

        $checkFile = file_exists($path . $newFilePath);
        if ($checkFile){
          $converter = new OfficeConverter($path . $newFilePath);
          //generates pdf file in same directory as test-file.docx
          $converter->convertTo($newFile.".pdf");
          $pdfConverted = '/upload/suratmasuk/' . $newFile.'.pdf';
          if (file_exists($path . $pdfConverted)){
            $saveFileToDb = File::create([
              'file_name' => $newFile.'.pdf',
              'file_path' => $pdfConverted,
              'original_name' => $newFile,
              'active' => '1',
              'created_at' => DB::raw('now()'),
              'created_by' => $loginid
            ]);
    
            $updateSK = $sM->update([
              'disposisi_file_id' => $saveFileToDb->id
            ]);
            $update = true;
          } else {
            throw new Exception();
          }
        } else {
          throw new Exception();
        }
      }
    } catch(\Exception $e){
      throw new Exception('Rollbacked');
      //lewat
    }
    return $update;
  }

  public static function cekKabid($positionid)
  {
    //kabid = child > 1
    return DB::table('gen_position')
      ->where('active', '1')
      ->where('parent_id', $positionid)
      ->whereNotIn('parent_id', [2, 3])
      ->count();
  }

  public static function saveDisSuratMasuk($inputs, $loginid, $positionid)
  {
    //Mulai Transaction
    $result = false;
   // DB::beginTransaction();
    try{
      $sM = SuratMasuk::where('active', '1')
        ->where('id', $inputs['surat_masuk_id']);
      
      switch($positionid){
        case 2:
          $sM->update([
            'kadin_id' => $loginid,
            'modified_at' => DB::raw('now()'),
            'modified_by' => $loginid
          ]);
          break;
        case 3:
          $sM->update([
            'sekretaris_id' => $loginid,
            'modified_at' => DB::raw('now()'),
            'modified_by' => $loginid
          ]);
        default:
          //lewat
          break;
      }
      //$appr = $inputs['is_approved']  ?? "false";
      foreach($inputs['to_user_id'] as $userid ){
        $q = DisSuratMasuk::create([
          'surat_masuk_id' => $inputs['surat_masuk_id'],
          'to_user_id' => $userid,
          'arahan' => $inputs['arahan'] ?? null,
          'is_tembusan' => $inputs['is_tembusan'] ?? null,
          'is_private' => $inputs['is_private'] ?? null,
          'log' => $inputs['log'],
          'logpos' => self::getLogPos($userid),
          'is_read' => '0',
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);

        if($q != null){
          $notif = array(
            'id_reference' => $inputs['surat_masuk_id'],
            'id_subreference' => $q->id,
            'display' => 'Surat Masuk - ' . ($inputs['nomor_surat'] ?? "_"),
            'type' => 'SURATMASUK'
          );
          $createNotif = NotificationRepository::createNotif($notif, $userid);
        }
      }

     // DB::commit();
      $result = true;
    }catch(\Exception $e){
      throw new exception($e->getMessage());
    }
    return $result;
  }

  public static function readDis($id)
  {
    $surat = DisSuratMasuk::where('id', $id)->where('active', '1')->first();

    if ($surat != null) {
      $surat->update([
        'is_read' => '1',
        'last_read' => DB::raw('now()')
      ]);
    }
    return $surat;
  }
  private static function getLogPos($userid)
  {
    $q = DB::table('gen_user as gu')->join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gu.id', $userid)
      ->select('position_name')
      ->first();
    return $q->position_name;
  }
}