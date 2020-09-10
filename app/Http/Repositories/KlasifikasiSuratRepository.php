<?php

namespace app\Http\Repositories;

use App\Model\KlasifikasiSurat;
use DB;
use Exception;

class KlasifikasiSuratRepository
{
  public static function getList($filter, $perm)
  {
    $data = KlasifikasiSurat::where('active', '1')
      ->select(
        'id',
        'kode_klasifikasi',
        'nama_klasifikasi',
        'detail',
        DB::raw("
          case when 1 = ". $perm['klasifikasiSurat_edit'] ." then 1 else 0 end as can_edit
        "),
        DB::raw("
          case when 1 = ". $perm['klasifikasiSurat_delete'] ." then 1 else 0 end as can_delete
        "))
      ->get();
    return $data;
  }

  public static function getById($respon, $id)
  {
    $q = DB::table('gen_klasifikasi_surat as gks')
      ->leftJoin('gen_user as cr', 'gks.created_by', 'cr.id')
      ->leftJoin('gen_user as md', 'gks.modified_by', 'md.id')
      ->where('gks.id', $id)
      ->where('gks.active', '1')
      ->select('gks.id',
        'kode_klasifikasi',
        'nama_klasifikasi',
        'detail',
        'gks.created_at',
        'cr.username as created_by',
        'gks.modified_at',
        'md.username as modified_by')
      ->first();

      if($q == null) {
        $respon['state_code'] = 400;
        array_push($respon['messages'], sprintf(trans('messages.dataNotFound'), 'Klasifikasi Surat'));
      } else {
        $respon['success'] = true;
        $respon['state_code'] = 200;
        $respon['data'] = $q;
      }
    return $respon;
  }

  public static function save($respon, $id, $inputs, $loginid)
  {
    try{
      $posisi = null;
      $mode = "";
      if ($id){
        $posisi = KlasifikasiSurat::where('active', '1')->where('id', $id)->firstOrFail();
        $posisi->update([
          'kode_klasifikasi' => $inputs['kode_klasifikasi'] ?? null,
          'nama_klasifikasi' => $inputs['nama_klasifikasi'],
          'detail' => $inputs['detail'] ?? null,
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        $mode = "Ubah";
      } else {
        $posisi = KlasifikasiSurat::create([
          'kode_klasifikasi' => $inputs['kode_klasifikasi'] ?? null,
          'nama_klasifikasi' => $inputs['nama_klasifikasi'],
          'detail' => $inputs['detail'] ?? null,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);
        $mode = "Simpan";
      }
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $posisi;
      array_push($respon['messages'], sprintf(trans('messages.succesSaveUpdate'),  $mode, $posisi->position_name));
    } catch(\Exception $e){
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }

    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $klasifikasi = KlasifikasiSurat::where('active', '1')->where('id', $id)->firstOrFail();

      $klasifikasi->update([
        'active' => '0',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      //$respon['data'] = $posisi;
      array_push($respon['messages'], sprintf(trans('messages.successDeleting'), 'Klasifikasi Surat'));
    } catch (\Exception $e) {
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function searchKlasifikasi($respon)
  {
    $q = KlasifikasiSurat::where('active','1')

      ->orderBy('nama_klasifikasi', 'ASC')
      ->select('id', DB::raw("kode_klasifikasi || ' - ' || nama_klasifikasi as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }
}