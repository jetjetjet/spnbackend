<?php

namespace app\Http\Repositories;

use App\Model\NomorSurat;
use DB;
use Exception;

class NomorSuratRepository
{
  public static function getAll($param)
  {
    $q = DB::table('gen_nomor_surat_keluar as nsk')
      ->join('gen_klasifikasi_surat as gks', 'gks.id', 'nsk.klasifikasi_id')
      ->join('surat_keluar as sk', 'sk.id', 'nsk.surat_keluar_id')
      ->join('gen_user as cr', 'cr.id', 'nsk.created_by')
      ->join('gen_position as crg', 'crg.id', 'cr.created_by')
      ->join('gen_user as skcr', 'skcr.id', 'sk.created_by')
      ->join('gen_position as skgp', 'skgp.id', 'skcr.position_id')
      ->where('nsk.active', '1')
      ->select(
        'gks.kode_klasifikasi',
        'gks.nama_klasifikasi',
        'nsk.surat_keluar_id',
        'prefix',
        'nsk.no_surat',
        'nks.no_agenda',
        'periode',

        DB::raw("cr.full_name || ' - ' || crg.position_name as penerbit_nomor_surat"),
        'nsk.created_at as penerbit_created_at',

        DB::raw("skcr.full_name || ' - ' || skgp.position_name as pembuat_surat"),
        'sk.created_at as pembuat_created_at'
      );

    $q = $param['order'] != null
      ? $q->orderByRaw("nsk.". $param['order'])
      : $q->orderBy('nsk.created_at', 'DESC');

    $q = $param['filter'] != null 
      ? $q->whereRaw("nsk.".$param['filter']. " like ? ", ['%' . trim($param['q']) . '%' ])
      : $q;

    $data = $q->paginate($param['per_page']);;

    return $data;
  }
}