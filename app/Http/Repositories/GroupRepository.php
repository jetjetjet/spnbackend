<?php
namespace app\Http\Repositories;

use App\Model\Group;
use App\Http\Repositories\ErrorLogRepository;
use DB;
use Exception;

class GroupRepository
{
  public static function groupList($respon, $perm)
  {
    $q = Group::where('active', '1')
      ->select('id',
      'group_code',
      'group_name',
      'created_at',
      'created_by',
      DB::raw("
        case when 1 = ". $perm['unit_edit'] ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when 1 = ". $perm['unit_delete'] ." then 1 else 0 end as can_delete
      "))
      ->get();
    $respon['state_code'] = 200;
    $respon['success'] = true;
    $respon['data'] = $q;

    return $respon;
  }

  public static function groupById($respon, $id)
  {
    $q = Group::where('active', '1')
      ->where('id', $id)
      ->first();
    if ($q == null){
      $respon['state_code'] = 400;
      $respon['messages'] = Array(sprintf(trans('messages.dataNotFound'), 'Unit'));
    } else {
      $respon['state_code'] = 200;
      $respon['success'] = true;
      $respon['data'] = $q;
    }
    
    return $respon;
  }

  public static function save($respon, $id, $inputs, $loginid)
  {
    try{
      $jabatan = null;
      $mode = "";
      if ($id){
        $jabatan = Group::where('active', '1')->where('id', $id)->firstOrFail();

        $jabatan->update([
          'group_name' => $inputs['group_name'],
          'group_code' => $inputs['group_code'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
        $mode = "Ubah";
      } else {
        $jabatan = Group::create([
          'group_name' => $inputs['group_name'],
          'group_code' => $inputs['group_code'],
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);
        $mode = "Simpan";
      }
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $jabatan;
      array_push($respon['messages'], sprintf(trans('messages.succesSaveUpdate'),  $mode, $jabatan->group_code));
    } catch(\Exception $e){
      $log =Array(
        'action' => 'SAV',
        'modul' => 'UNITKERJA',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $cekRef = DB::table('gen_position')->where('active','1')->where('group_id', $id)->first();
      if($cekRef != null){
        array_push($respon['messages'], sprintf(trans('messages.errorDelReference'), 'Unit Kerja'));
        return $respon;
      }

      $jabatan = Group::where('active', '1')->where('id', $id)->firstOrFail();

      $jabatan->update([
        'active' => 'ASD',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($respon['messages'], sprintf(trans('messages.successDeleting'), 'Unit'));
    }catch(\Exception $e){
      $log =Array(
        'action' => 'DEL',
        'modul' => 'UNITKERJA',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? "NOT_RECORDED"
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      array_push($respon['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function searchGroup($respon)
  {
    $q = Group::where('active','1')
      ->orderBy('group_name')
      ->select('id', DB::raw("group_code || ' - ' || group_name as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }
}