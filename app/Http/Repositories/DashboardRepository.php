<?php

namespace app\Http\Repositories;

use DB;
use Exception;

class DashboardRepository
{
  public static function getTugas($filter, $loginid)
  {
    $qSM = DB::table('surat_masuk as sm')
      ->join('dis_surat_masuk as dsm', 'dsm.surat_masuk_id', 'sm.id')
      ->join('gen_user as cr', 'cr.id', 'dsm.created_by')
      ->join('gen_position as pcr', 'pcr.id', 'cr.position_id')
      ->where('dsm.active', '1')
      ->where('sm.active', '1')
      ->where('dsm.to_user_id', $loginid)
      ->whereRaw('(is_read  is false and last_read is null)')
      ->select(
        DB::raw("'Surat Masuk' as tipe_masuk"),
        'sm.id',
        'dsm.id',
        'perihal',
        'nomor_surat',
        'tgl_surat',
        'asal_surat',
        DB::raw("case when dsm.log = 'CREATED' then 'Dibuat'  ||  cr.full_name
        when dsm.log = 'DISPOSITION' then 'Didisposisikan'  ||  cr.full_name
        else '' end as status"),
        'pcr.position_name');

    $q = DB::table('surat_keluar as sk')
      ->join('dis_surat_keluar as dsk', 'dsk.surat_keluar_id', 'sk.id')
      ->join('gen_user as cr', 'cr.id', 'dsk.created_by')
      ->join('gen_position as pcr', 'pcr.id', 'cr.position_id')
      ->where('dsk.active', '1')
      ->where('sk.active', '1')
      ->where('dsk.tujuan_user_id', $loginid)
      ->whereRaw('(is_read  is false and last_read is null)')
      ->select(
        DB::raw("'Surat Masuk' as tipe_surat"),
        'sk.id',
        'dsk.id as dis_id',
        'sk.hal_surat',
        DB::raw("coalesce(sk.nomor_surat, 'Belum diisi.') as nomor_surat"),
        'sk.tgl_surat',
        'sk.tujuan_surat',
        DB::raw("case when is_approve = '1' and surat_log = 'CREATED' then 'Konsep - '|| cr.full_name
        when is_approve = '1' and surat_log = 'REVISED' then 'Revisi - '|| cr.full_name
        when is_approve = '1' and coalesce(is_verify,'0') = '0' and surat_log = 'REJECTED' then 'Ditolak - ' ||  cr.full_name
        when is_approve = '1' and is_verify = '1' and surat_log = 'APPROVED' then 'Disetujui - ' ||  cr.full_name
        when is_approve = '1' and is_verify = '0' and surat_log = 'VERIFY_REJECTED' then 'Ditolak - ' ||  cr.full_name
        when is_verify = '1' and is_agenda = '1' and surat_log = 'VERIFIED' then 'Diverifikasi - ' || cr.full_name
        when is_agenda = '1' and is_sign = '1' and surat_log = 'AGENDA' then 'Diagenda - ' ||  cr.full_name
        when is_agenda = '1' and is_sign = '1' and surat_log = 'SIGNED' then 'Ditandatangani - ' || cr.full_name
        when is_approve = '1' and is_verify = '0' and is_sign = '0' and surat_log = 'SIGN_REJECTED' then 'Ditolak - ' || cr.full_name
        else '' end as status_surat
      "),
      'pcr.position_name')
      ->union($qSM)
      ->get();
    
    return $q;
  }
}