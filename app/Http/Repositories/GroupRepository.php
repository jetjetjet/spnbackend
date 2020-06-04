<?php
namespace app\Http\Repositories;

use App\Model\Group;
use DB;
use Exception;

class GroupRepository
{
  public static function groupList($respon)
  {
    $q = Group::where('active', '1')->get();
    $respon['state_code'] = 200;
    $respon['success'] = true;
    $respon['data'] = $q;

    return $respon;
  }

  public static function groupById($respon)
  {
    $q = Group::where('active', '1')
      ->where('gen_group.id', $id)
      ->first();
    if ($q == null){
      $respon['state_code'] = 400;
      $respon['messages'] = trans('messages.dataNotFound', ["item" => $id]);
    } else {
      $respon['state_code'] = 200;
      $respon['success'] = true;
      $respon['data'] = $q;
    }
    
    return $respon;
  }

  public static function save($respon, $id, $input, $loginid)
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
      array_push($respon['messages'], trans('messages.succesSaveUpdate', ["item" => $jabatan->group_code, "item2" => $mode]));
    } catch(\Exception $e){
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $jabatan = Group::where('active', '1')->where('id', $id)->firstOrFail();

      $jabatan->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $respon['success'] = true;
      $respon['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $jabatan->group_code]));
    }catch(\Exception $e){
      array_push($respon['messages'], $e->getMessage());
    }
  }
}