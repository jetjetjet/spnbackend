<?php

namespace app\Http\Repositories;

use App\User;
use App\Helpers\Helper;
use DB;
use Exception;

class UserRepository
{
  public static function getUserList($filter)
  {
    $data = new \stdClass();
    $q = User::leftJoin('gen_position as gg', function($sq){
      $sq->on('gg.id', 'position_id')
        ->on('gg.active', DB::raw("'1'")); })
      ->where([
        'gen_user.active' => '1'
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
			$q = $q->orderBy('gen_user.created_at');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select('gen_user.id', 
      'gg.position_name',
      'gg.id as position_id',
      'username', 
      'full_name',
      'email',
      'phone',
      'address',
      'last_login'
      )->get();

    return $data;
  }

  public static function getUserById($id, $result)
  {
    $q = User::where('active', '1')
      ->where('gen_user.id', $id)
      ->first();
    if ($q == null){
      $result['state_code'] = 400;
      $result['messages'] = trans('messages.dataNotFound', ["item" => $id]);
    } else {
      $result['state_code'] = 200;
      $result['success'] = true;
      $result['data'] = $q;
    }
    return $result;
  }

  public static function save($id, $result, $inputs, $userLogin)
  {
    try{
      $user = null;
      $mode = "";
      if ($id){
        $user = User::where('active', '1')->where('id', $id)->firstOrFail();

        $user->update([
          'full_name' => $inputs['full_name'],
          'position_id' => $inputs['position_id'] ?? null,
          'phone' => $inputs['phone'],
          'address' => $inputs['address'],
          'ttl' => $inputs['nip'] ?? null,
          'jenis_kelamin' => $inputs['jenis_kelamin'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $userLogin
        ]);
        $mode = "Ubah";
      } else {
        $user = User::create([
          'username' => $inputs['username'],
          'position_id' => $inputs['position_id'] ?? null,
          'nip' => $inputs['nip'],
          'password' => bcrypt($inputs['password']),
          'ttl' => $inputs['nip'] ?? null,
          'jenis_kelamin' => $inputs['jenis_kelamin'],
          'email' => $inputs['email'],
          'full_name' => $inputs['full_name'],
          'phone' => $inputs['phone'],
          'address' => $inputs['address'],
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $userLogin
        ]);
        $mode = "Simpan";
      }
      $result['success'] = true;
      $result['state_code'] = 200;
      $result['data'] = $user;
      array_push($result['messages'], trans('messages.succesSaveUpdate', ["item" => $user->username, "item2" => $mode]));
    } catch(\Exception $e){
      $result['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
    }

    return $result;
  }

  public static function deleteUserById($id, $result, $loginid)
  {
    try{
      $user = User::where('active', '1')->where('id', $id)->firstOrFail();

      $user->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $user->username]));
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
    return $result;
  }

  public static function changePassword($id, $result, $inputs, $loginid)
  {
    try{
      $user = User::where('active', '1')->where('id', $id)->firstOrFail();

      $user->update([
        'password' => bcrypt($inputs['password']),
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successUpdatePassword', ["item" => $user->username]));
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
    }
    return $result;
  }

  public static function searchUser($respon)
  {
    $q = User::leftJoin('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active','1')
      ->orderBy('full_name')
      ->select('gen_user.id', DB::raw("full_name || ' - ' || coalesce(position_name,'') as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }

  public static function saveFoto($id, $respon, $inputs, $loginid)
  {
    try{
      $file = Helper::prepareFile($inputs, '/upload/photo');
      if ($file){
        $user = User::where('active', '1')->where('id', $id)->firstOrFail();

        $user->update([
          'path_foto' => '/upload/photo/'. $file->newName,
          'modified_at' => \Carbon\Carbon::now(),
          'modified_by' => $loginid
        ]);

        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successUpdatedPhoto'));
      } 
    }catch (\Exception $e){
      $respon['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
    }
    return $respon;
  }
}