<?php

namespace app\Http\Repositories;

use App\Model\PositionMenu;
use DB;
use Exception;

class PermissionRepository
{
  public static function getPositionPerm($idPosisi)
  {
    $perm = [];
    $permissions = DB::table('gen_positionmenu')
      ->where('active', '1')
      ->where('position_id', $idPosisi)
      ->select(DB::raw("string_agg(permissions, ',') as permissions"))->first();
    if($permissions->permissions != null)
    $perm = explode(",",$permissions->permissions);

    return $perm;
  }

  public static function savePermission($respon, $idPosisi, $inputs, $loginid)
  {
    try{
      //$inputs['permissions'] = !empty($inputs['permissions']) ? $inputs['permissions'] : array();
      $inputs['perm'] = $inputs['permissions'];
      $checkData = PositionMenu::where('active', '1')->where('position_id', $idPosisi)->first();
      if($checkData	!= null){
        $checkData->update([
          'permissions' => $inputs['perm'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);
        array_push($respon['messages'], trans('messages.successUpdatePermissions'));
      } else {
        $cr = PositionMenu::create([
          'position_id' => $idPosisi,
          'permissions' => $inputs['perm'],
          'menu_id' => 0,
          'created_at' => DB::raw('now()'),
          'active' => '1',
          'created_by' => $loginid
        ]);
        array_push($respon['messages'], trans('messages.successSavePermissions'));
      }
      $respon['state_code'] = 200;
      $respon['success'] = true;

    } catch(Exception $e){
      $respon['state_code'] = 500;
      array_push($respon['messages'], trans('messages.failSavePermissions'));
    }
    return $respon;
  }
}