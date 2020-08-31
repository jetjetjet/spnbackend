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
    // $qdsk = DB::table('dis_surat_keluar')
    //   ->where('active', '1')
    //   ->orderBy 
    $q = DB::table('surat_keluar as sk')
      ->join('dis_surat_keluar as dsk',function($query){
        $query->on('dsk.surat_keluar_id', 'sk.id')
        ->on('dsk.active', DB::raw("'1'"));
      })
      ->join('gen_user as cr', 'cr.id', 'dsk.created_by')
      ->join('gen_position as gp', 'gp.id', 'cr.position_id')
      ->join('gen_group as gg', 'gg.id', 'gp.group_id')
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
      $q = $q->orderBy('dsk.created_at', 'DESC');
    } else {
      $q = $q->orderBy('sk.id', 'DESC');
      $q = $q->orderBy('dsk.created_at', 'DESC');
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
      'dsk.log',
      'position_name',
      DB::raw("coalesce(nomor_agenda, 'belum diisi') as nomor_agenda"),
      DB::raw("coalesce(nomor_surat, 'belum diisi') as nomor_surat"),
      DB::raw("coalesce(to_char(tgl_surat, 'dd-mm-yyyy'), 'belum diisi') as tgl_surat"),
      DB::raw("case when dsk.log = 'signed' then 'Sudah ditandatangani'
        when dsk.log = 'agenda' then 'Menunggu ditanda tangani'
        when dsk.log = 'disposition' and dsk.is_approved = '1' then 'Disetujui'
        when dsk.log = 'disposition' and dsk.is_approved = '0' then 'Ditolak'
        when dsk.log = 'signed' then 'Ditandatangani'
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
        DB::raw("case when sk.is_approved = '0' and 1 =" . $perms['suratKeluar_ttd'] . " then 1 else 0 end as can_approve"),
        DB::raw("case when sk.is_approved = '0' and sk.is_disposition = '0' and 1 =" . $perms['suratKeluar_disposition'] . " then 1 else 0 end as can_disposition"),
        DB::raw("case when sk.is_approved = '0'  and 1 =" . $perms['suratKeluar_agenda'] . " then 1 else 0 end as can_agenda")
      )->first();
    
    if($header != null){
      $data = new \stdClass();

      $disposisi = DB::table('dis_surat_keluar as dsk')
        ->join('gen_user as to', 'dsk.created_by', 'to.id')
        ->leftJoin('gen_position as gp', 'to.position_id', 'gp.id')
        ->leftJoin('gen_file as f', 'file_id', 'f.id')
        ->where('dsk.active', '1')
        ->where('dsk.surat_keluar_id', $id)
        ->orderBy('dsk.created_at', 'ASC')
        ->select('dsk.id', 
          DB::raw("case when log = 'create' then 'Surat dibuat oleh: '
            when log = 'disposition' and is_approved = '0' then 'Surat ditolak dan dikembalikan oleh: '
            when log = 'disposition' and is_approved = '1' then 'Surat disetujui dan diteruskan oleh: '
            when log = 'agenda' then 'Surat diagenda oleh: '
            when log = 'signed' then 'Surat ditandatangani oleh: '
            else '' end as label_disposisi"),
          'surat_keluar_id', 
          'dsk.created_by',
          'dsk.is_approved',
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
      if ($tSurat == null || $tSurat->created_by != $loginid){
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
        
        $result['id'] = $tSurat->id ?: $id;
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
        'is_disposition' => '0',
        'is_agenda' => '0',
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
          'tujuan_surat' => $inputs['tujuan_surat'],
          'keterangan' => 'Draft',
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
      ->where('is_disposition', '1')
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
      //'nomor_agenda' => $inputs['nomor_agenda'],
      //'nomor_surat' => $inputs['nomor_surat'],
     // 'tgl_surat' => $inputs['tgl_surat'],
      //'file_id' => $respon['file_id'],
      'modified_at' => DB::raw("now()"),
      'modified_by' => $loginid,
      'is_agenda' => '1'
    ]);

    $dataDis = Array(
      'surat_keluar_id' => $sm->id,
      'tujuan_user' => $sm->approval_user,
      'file_id' => $respon['file_id'],
      'log' => "agenda",
      'keterangan' => "" // $inputs['keterangan'],
    );
    $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
    if($dis == null){
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

  public static function signSurat($respon, $id, $inputs, $loginid)
  {
    $sk = SuratKeluar::where('id', $id)->where('active', '1')->whereNull('approved_at')->first(); 
    if ($sk != null){
      $isiKode = Str::random(12);
      $data = DB::table('dis_surat_keluar as dsk')
        ->join('gen_file as gf', 'gf.id', 'dsk.file_id')
        ->where('dsk.active', '1')
        ->where('surat_keluar_id', $id)
        ->where('log', 'agenda')
        ->orderBy('dsk.created_at', 'desc')
        ->select('file_path', 'file_name', 'original_name')
        ->first();

      $user = DB::table('gen_user as gu')
        ->where('active', '1')
        ->where('id', $loginid)
        ->select('ttd', 'email', 'username', 'full_name')
        ->first();
    
      if($data->file_path != null)
      {

        $pdf = new Fpdi();
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


              //   // import page 1
              //   $tplId = $pdf->importPage(1);
              //   // use the imported page and place it at point 10,10 with a width of 100 mm
              //   $pdf->useTemplate($tplId);
            
              //   //set certificate file
              //   $certificate = 'file://'. realpath('../stack/certificates/public/'. $user->username .'.crt');
              //   $private_key = 'file://'. realpath('../stack/certificates/private/'. $user->username .'.key');
              //   //dd(file_get_contents($certificate));
              //   $info = array(
              //     'Name' => $user->full_name,
              //     'Location' => 'Kerinci',
              //     'Keterangan' => $inputs['keterangan'],
              //     'Email' => $user->email
              //   );
            
              //   $pdf->setSignature($certificate, $private_key, '', '', 2, $info);
                
              //   //$pdf->Image(base_path() . $user->ttd, 175, 262, 15, 15, 'PNG');

                
              // // $isiKode = Crypt::encryptString('Surat No. ' . $sk->nomor_surat . ' ditandatangani digital oleh ' . $user->full_name);
            
              // // QRCODE,L : QR-CODE Low error correction
              //  $pdf->write2DBarcode($isiKode, 'QRCODE,L', 170, 260, 15, 15, $style, 'N');

              //   $pdf->setSignatureAppearance(170, 260, 15, 15);

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
          'is_approved' => '1',
          'approved_by' => $loginid,
          'approved_at' => DB::raw('now()'),
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        $dataDis = Array(
          'surat_keluar_id' => $id,
          'tujuan_user' => $sk->created_by,
          'file_id' => $newFile->id,
          'log' => "signed",
          'keterangan' => $inputs['keterangan'],
        );
        $dis = DisSuratKeluarRepository::saveDisSuratKeluar($dataDis, $loginid);
        //$pdf->reset();
        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successSignSuratKeluar'));
      }
    } else {
      array_push($respon['messages'], trans('messages.suratAlreadySign'));
    }
    return $respon;
  }

  public static function replaceString()
  {


// Path to directory with tcpdf.php file.
// Rigth now `TCPDF` writer is depreacted. Consider to use `DomPDF` or `MPDF` instead.

    $path = base_path() . '/upload/suratkeluar/';
    // $docx = new \PhpOffice\PhpWord\TemplateProcessor($path . 'tes.docx');
    // $docx->setValue('{NAME}', 'Simba');
    // $docx->saveAs($path . 'tes1.docx');
    Settings::setPdfRendererPath(base_path() .'/vendor/dompdf/dompdf');

    Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
    $phpWord = IOFactory::load($path . 'tes1.docx', 'Word2007');
    $xmlWriter = IOFactory::createWriter($phpWord, 'PDF');
    $xmlWriter->save($path . 'tes1.pdf');  
    
    dd("OK");
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
      ->leftJoin('gen_file as gf', 'gf.id', 'dsk.file_id')
      ->where('dsk.active', '1')
      ->where('log', DB::raw("'disposition'"))
      ->where('surat_keluar_id', $id)
      ->whereNotNull('is_approved')
      ->select('file_path', 'file_name', 'original_name', 'is_approved')
      ->first();

      //nanti diriview
      if($getFile->file_path == null && $getFile->is_approved){
        $getFile = DB::table("dis_surat_keluar as dsk")
          ->join('gen_file as gf', 'gf.id', 'dsk.file_id')
          ->where('dsk.active', '1')
          ->where('log', 'create')
          ->where('surat_keluar_id', $id)
          ->orderBy('dsk.created_at', 'DESC')
          ->select('file_path', 'file_name', 'original_name', 'is_approved')
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
        $newFilePath = '/upload/suratkeluar/' . $newFile.'.pdf';
        
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
          ->update([
            'nomor_surat' => $noSurat,
            'nomor_agenda' => $noAgenda,
            'tgl_surat' => $inputs['tgl_agenda'],
            'agenda_at' => DB::raw("now()"),
            'agenda_by' => $loginid,
            'modified_at' => DB::raw('now()'),
            'agenda_file_id' => $saveFileToDb->id,
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
        array_push($respon['messages'], trans('messages.errorAdministrator'));
      }
    } else {
      $respon['success'] = false;
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.dataNotFound'));
    }
    return $respon;
  }
}