<?php

namespace app\Http\Repositories;

use App\Model\Position;
use DB;
use Exception;

class PositionRepository
{
  public static function getList($filter)
  {
    $data = new \stdClass();
    $q = Position::leftJoin('gen_group as gg', function($sq){
      $sq->on('gg.id', 'group_id')
        ->on('gg.active', DB::raw("'1'")); })
      ->where([
        'gen_position.active' => '1'
      ]);

    if($filter->search){
			foreach($filter->search as $qCol){
				$sCol = explode('|', $qCol);
				$fCol = str_replace('"', '', $sCol[0]);
				$q = $q->where($sCol[0], 'like', '%'.$sCol[1].'%');
			}
    }
    
    $qCount = $q->count();

    if ($filter->sortColumns){
			$order = $filter->sortColumns[0];
			$q = $q->orderBy($order->column, $order->order);
		} else {
			$q = $q->orderBy('gen_position.created_at');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select('gen_position.id', 
      'gg.group_name',
      'gg.id as group_id',
      'position_name', 
      'position_type',
      )->get();
    return $data;
  }

  public static function getById($respon, $id)
  {
    $q = Position::leftJoin('gen_group as gg', 'group_id', 'gg.id')
      ->leftJoin('gen_user as cr', 'gen_position.created_by', 'cr.id')
      ->leftJoin('gen_user as md', 'gen_position.modified_by', 'mo.id')
      ->where('gen_position.id', '$id')
      ->where('gen_position.active', '1')
      ->select('gen_position.id',
        'group_id',
        'gg.group_name',
        'position_name',
        'position_type',
        'detail',
        'gen_position.created_at',
        'cr.username as created_by',
        'gen_position.modified_at',
        'md.username as modified_by')
      ->first();

      if($q == null) {
        $respon['state_code'] = 400;
        array_push($respon['messages'], trans('messages.dataNotFound'));
      } else {
        $respon['success'] = true;
        $respon['state_code'] = 200;
      }
    return $respon;
  }

  public static function save($respon, $id, $inputs, $loginid)
  {
    try{
      $posisi = null;
      $mode = "";
      if ($id){
        $posisi = Position::where('active', '1')->where('id', $id)->firstOrFail();

        $posisi->update([
          'group_id' => $inputs['group_id'] ?? null,
          'position_name' => $inputs['position_name'],
          'position_type' => $inputs['position_type'],
          'detail' => $inputs['address'] ?? null,
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        $mode = "Ubah";
      } else {
        $posisi = Position::create([
          'group_id' => $inputs['group_id'] ?? null,
          'position_name' => $inputs['position_name'],
          'position_type' => $inputs['position_type'],
          'detail' => $inputs['address'] ?? null,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);
        $mode = "Simpan";
      }
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $user;
      array_push($respon['messages'], trans('messages.succesSaveUpdate', ["item" => $user->position_name, "item2" => $mode]));
    } catch(\Exception $e){
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }

    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $posisi = Position::where('active', '1')->where('id', $id)->firstOrFail();

      $posisi->update([
        'active' => '1',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      //$respon['data'] = $posisi;
      array_push($respon['messages'], trans('messages.successDeleting', ["item" => $user->position_name]));
    } catch (\Exception $e) {
      $respon['state_code'] = 500;
      array_push($respon['messages'], $e->getMessage());
    }
    return $respon;
  }

  public static function searchPosition($respon)
  {
    $q = Position::where('active','1')
      ->orderBy('position_name')
      ->select('id', DB::raw("position_name || ' - ' || position_type as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }

  public static function getPositionMenuById($respon, $id)
  {
    // $q = Position::join('gen_positionmenu as gpm', function($q){
    //     $q->on('gpm.position_id', 'gen_position.id')
    //       ->on('gpm.active', DB::raw("'1'"));
    //   })->join('gen_menu as gm', function($q){
    //     $q->on('gpm.menu_id', 'gm.id')
    //       ->on('gm.active', DB::raw("'1'"));
    //   )->select('menu_name')
  }
}