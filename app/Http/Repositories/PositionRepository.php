<?php

namespace app\Http\Repositories;

use App\Model\Position;
use DB;
use Exception;

class PositionRepository
{
  public static function getList($filter, $perm)
  {
    $data = new \stdClass();
    $q = Position::leftJoin('gen_group as gg', function($sq){
      $sq->on('gg.id', 'group_id')
        ->on('gg.active', DB::raw("'1'")); })
      ->where([
        'gen_position.active' => '1'
      ]);

    // if($filter->search){
		// 	foreach($filter->search as $qCol){
		// 		$sCol = explode('|', $qCol);
		// 		$fCol = str_replace('"', '', $sCol[0]);
		// 		$q = $q->where($sCol[0], 'like', '%'.$sCol[1].'%');
		// 	}
    // }
    
    // $qCount = $q->count();

    // if ($filter->sortColumns){
		// 	$order = $filter->sortColumns[0];
		// 	$q = $q->orderBy($order->column, $order->order);
		// } else {
		// 	$q = $q->orderBy('gen_position.created_at');
    // }
    
		// $q = $q->skip($filter->offset);
    // $q = $q->take($filter->limit);
    
    //$data->totalCount = $qCount;
    $data = $q->select('gen_position.id', 
      'gg.group_name',
      'gg.id as group_id',
      'position_name', 
      'position_type',
      DB::raw("
        case when 1 = ". $perm['jabatan_edit'] ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when 1 = ". $perm['jabatan_delete'] ." then 1 else 0 end as can_delete
      "),
      DB::raw("
        case when 1 = ". $perm['jabatan_savePermission'] ." then 1 else 0 end as can_permissions
      "))->get();
    return $data;
  }

  public static function getById($respon, $id)
  {
    $q = Position::leftJoin('gen_group as gg', 'group_id', 'gg.id')
      ->join('gen_user as cr', 'gen_position.created_by', 'cr.id')
      ->leftJoin('gen_user as md', 'gen_position.modified_by', 'md.id')
      ->where('gen_position.id', $id)
      ->where('gen_position.active', '1')
      ->select('gen_position.id',
        'group_id',
        'gg.group_name',
        'position_name',
        'position_type',
        'is_parent',
        'parent_id',
        DB::raw("(select tgp.position_name || ' - ' || tgp.position_type from gen_position tgp where tgp.id = gen_position.parent_id) as parent_name"),
        'detail',
        'gen_position.created_at',
        'cr.username as created_by',
        'gen_position.modified_at',
        'md.username as modified_by')
      ->first();

      if($q == null) {
        $respon['state_code'] = 400;
        array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Jabatan'));
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
        $posisi = Position::where('active', '1')->where('id', $id)->firstOrFail();
        $posisi->update([
          'group_id' => $inputs['group_id'] ?? null,
          'position_name' => $inputs['position_name'],
          'position_type' => $inputs['position_type'],
          'is_parent' => $inputs['is_parent'] ?? null,
          'parent_id' => $inputs['parent_id'] ?? null,
          'detail' => $inputs['detail'] ?? null,
          'modified_at' => DB::raw('now()'),
          'modified_by' => $loginid
        ]);

        $mode = "Ubah";
      } else {
        $posisi = Position::create([
          'group_id' => $inputs['group_id'] ?? null,
          'position_name' => $inputs['position_name'],
          'position_type' => $inputs['position_type'],
          'detail' => $inputs['detail'] ?? null,
          'is_parent' => $inputs['is_parent'] ?? null,
          'parent_id' => $inputs['parent_id'] ?? null,
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
      array_push($respon['messages'], sprintf(trans('messages.errorSaveUpdate'),'Jabatan.'));
    }

    return $respon;
  }

  public static function delete($respon, $id, $loginid)
  {
    try{
      $posisi = Position::where('active', '1')->where('id', $id)->firstOrFail();

      if ($posisi->id == 1 || $posisi->id == 2)
        throw new exception;

      $posisi->update([
        'active' => '0',
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
      
      $respon['success'] = true;
      $respon['state_code'] = 200;
      //$respon['data'] = $posisi;
      array_push($respon['messages'], sprintf(trans('messages.successDeleting', $posisi->position_name)));
    } catch (\Exception $e) {
      $respon['state_code'] = 500;
      array_push($respon['messages'], sprintf(trans('messages.errorDeleting'), 'Jabatan.'));
    }
    return $respon;
  }

  public static function searchPosition($respon)
  {
    $q = Position::where('active','1')
      ->whereNotIn('id', [DB::raw("select position_id from gen_user where active = '1' and position_id is not null")])
      ->orderBy('position_name', 'ASC')
      ->select('id', DB::raw("position_name || ' - ' || position_type as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }

  public static function searchParentPosition($respon)
  {
    $q = Position::where('active','1')
      ->where('is_parent', '1')
      ->orderBy('position_name', 'ASC')
      ->select('id', DB::raw("position_name || ' - ' || position_type as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }
}