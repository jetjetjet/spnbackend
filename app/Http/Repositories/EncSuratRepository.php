<?php
namespace app\Http\Repositories;

use App\Model\EncSurat;
use DB;
use Exception;

class EncSuratRepository
{
  public static function validasi($respon, $inputs)
  {
    $q = DB::table('enc_surat as es')
      ->join('surat_keluar as sk', 'surat_keluar_id', 'sk.id')
      ->join('gen_klasifikasi_surat as gks', 'gks.id', 'sk.klasifikasi_id')
      ->join('gen_user as cr', 'cr.id', 'sk.created_by')
      ->join('gen_user as td', 'td.id', 'sk.signed_by')
      ->join('gen_file as gf', 'gf.id', 'sk.file_id')
      ->where('sk.active', '1')
      ->where('es.active', '1')
      ->where('key', $inputs['key'])
      ->select(
        'nomor_agenda',
        'nomor_surat',
        'tgl_surat',
        'jenis_surat',
        'gks.nama_klasifikasi',
        'sifat_surat',
        'tujuan_surat',
        'hal_surat',
        'lampiran_surat',
        'gf.file_path',
        'gf.original_name',
        'td.full_name as ttd_name',
        'cr.full_name as created_name',
        DB::raw("'Kode Surat Terverifikasi' as status")
      )->first();

    if($q == null) {
      $respon['state_code'] = 400;
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Nomor Surat'));
    } else {
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $q;
    }
    return $respon;
  }
}