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
use Illuminate\Support\Facades\File as FaFile;

class DisSuratMasukRepository
{

  public static function disSuratMasuk($respon, $inputs, $loginid, $positionid)
  {
    try{
      $inputs['log'] = 'DISPOSITION';
      $childKabid = self::cekSMStatus($inputs['surat_masuk_id'], $positionid);
      DB::transaction(function () use (&$respon, $inputs, $loginid, $positionid, $childKabid){
        $valid = self::saveDisSuratMasuk($respon, $inputs, $loginid, $positionid);
        if(!$valid) return;
        if($childKabid > 1){
          $valid = self::generateLembarDisposisi($respon, $inputs, $loginid);
          if(!$valid) return;
        }

        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $valid;
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

  public static function getDispDetail($suratid, $userid)
  {
    return DB::table('dis_surat_masuk as dsm')
      ->join('gen_user as gu', 'gu.id', 'dsm.created_by')
      ->join('gen_position as gp', 'gp.id', 'gu.position_id')
      ->where('dsm.surat_masuk_id', $suratid)
      ->where('dsm.created_by', $userid)
      ->orderBy('dsm.created_at', 'DESC')
      ->select(
        DB::raw("(gu.full_name || ' - ' || gp.position_name || ':') as dis_label"),
        DB::raw("coalesce(dsm.arahan, '-') as arahan"),
      )->first();
  }

  public static function generateLembarDisposisi(&$respon, $inputs, $loginid)
  {
    $update = false;
    $sM = SuratMasuk::where('surat_masuk.active', '1')
      ->where('surat_masuk.id', $inputs['surat_masuk_id'])
      ->select(
          'asal_surat', 
          'perihal', 
          'nomor_surat', 
          DB::raw("to_char(tgl_surat, 'dd-mm-yyyy') as tgl_surat"),
          'sekretaris_id',
          'disposisi_file_id',
          'kadin_id', 
          'kabid_id')
      ->first();

    //Mulai Transaction
    try{
      if($sM != null && $sM->disposisi_file_id == null){
        $kadin = self::getDispDetail($inputs['surat_masuk_id'], $sM->kadin_id);
        $sekre = self::getDispDetail($inputs['surat_masuk_id'], $sM->sekretaris_id);
        $kabid = self::getDispDetail($inputs['surat_masuk_id'], $loginid);

        $path = base_path();
        // $path = '/home/admin/web/apisurat.disdikkerinci.id/public_html';
        $newFile = time()."_LembarDisposisi_". $sM->asal_surat;
        $newFilePath = '/upload/suratmasuk/' . $newFile.'.docx';
        
        $docx = new \PhpOffice\PhpWord\TemplateProcessor($path . "/upload/suratmasuk/TEMPLATEDISPOSISI.docx");
        //$docx->setValue(array('{NOMOR_SURAT}' => $noSurat, '{TANGGAL_SURAT}' => $inputs['tgl_teks']));
            
        $docx->setValue('{NOMOR_SURAT}', $sM->nomor_surat);
        $docx->setValue('{TGL_SURAT}', $sM->tgl_surat);
        $docx->setValue('{PERIHAL}', $sM->perihal);
        $docx->setValue('{ASAL_SURAT}', $sM->asal_surat);
        $docx->setValue('{LBL_SEKRE}', $sekre->dis_label);
        $docx->setValue('{DISPOSISI_SEKRE}', $sekre->arahan);
        $docx->setValue('{LBL_KADIN}', $kadin->dis_label);
        $docx->setValue('{DISPOSISI_KADIN}', $kadin->arahan);
        $docx->setValue('{LBL_KABID}', $kabid->dis_label);
        $docx->setValue('{DISPOSISI_KABID}', $kabid->arahan);
        $docx->saveAs( $path . $newFilePath, TRUE);

        // $checkFile = file_exists($path . $newFilePath);
        $checkFile = FaFile::exists($path . $newFilePath);
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
            
            $updateDisSm = SuratMasuk::where('surat_masuk.id', $inputs['surat_masuk_id'])
            ->update([
              'kabid_id' => $loginid,
              'disposisi_file_id' => $saveFileToDb->id,
              'modified_at' => DB::raw('now()'),
              'modified_by' => $loginid
            ]);
            $update = true;
          } else {
            throw new Exception();
          }
        } else {
          throw new Exception();
        }
      } else {
        array_push($respon['messages'], 'Lembar Disposisi sudah dibuat');
        $update = true;
      }
      
    } catch(\Exception $e){
      throw new Exception($e->getMessage());
      //lewat
    }
    return $update;
  }

  public static function cekSMStatus($SMid, $positionid)
  {
    //kabid / is_parent sekretaris = child > 1
    $count = 0;
    $sM = SuratMasuk::where('active', '1')
        ->where('id', $SMid)
        ->whereNotNull('kadin_id')
        ->whereNotNull('sekretaris_id')
        ->first();
    if($sM != null){
      $count = DB::table('gen_position')
        ->where('active', '1')
        ->where('parent_id', $positionid)
        ->whereRaw("(coalesce(is_kadin,'0') = '0')")
        ->count();
    }

    return $count;
  }

  public static function saveDisSuratMasuk(&$respon, $inputs, $loginid, $positionid)
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
      $counter = 0;
      foreach($inputs['to_user_id'] as $userid ){
        $validasi = DisSuratMasuk::where('surat_masuk_id', $inputs['surat_masuk_id'])
          ->where('active', '1')
          ->where('to_user_id', $userid)
          ->where('created_by', $loginid)
          ->first();
        //cek duplikasi
        if($validasi != null) continue;

        $q = DisSuratMasuk::create([
          'surat_masuk_id' => $inputs['surat_masuk_id'],
          'to_user_id' => $userid,
          'arahan' => $inputs['arahan'] ?? null,
          'is_tembusan' => $inputs['is_tembusan'] ?? null,
          'is_private' => $inputs['is_private'] ?? null,
          'log' => $inputs['log'],
          'logpos' => self::getLogPosSM($userid), 
          'is_read' => '0',
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);

        if($q != null){
          $counter++;
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
      array_push($respon['messages'], 'Surat berhasil diteruskan kepada ' . $counter . ' orang.');
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
  private static function getLogPosSM($userid)
  {
    $q = DB::table('gen_user as gu')->join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gu.id', $userid)
      ->select('position_name')
      ->first();
    return $q->position_name;
  }
}