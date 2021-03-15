<?php

namespace app\Http\Repositories;

use App\User;
use App\Helpers\Helper;
use App\Http\Repositories\ErrorLogRepository;
use DB;
use Exception;

class UserRepository
{
  public static function getUserList($param, $perm)
  {
    $data = User::leftJoin('gen_position as gg', function($sq){
      $sq->on('gg.id', 'position_id')
        ->on('gg.active', DB::raw("'1'")); })
      ->where([
        'gen_user.active' => '1'])
      ->select('gen_user.id', 
      'nip',
      DB::raw("to_char(ttl, 'yyyy-mm-dd') as ttl"),
      'jenis_kelamin',
      'position_id',
      'gg.position_name',
      'gg.id as position_id',
      'username', 
      'full_name',
      'email',
      'phone',
      'address',
      'last_login',
      DB::raw("
        case when 1 = ". $perm['user_edit'] ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when 1 = ". $perm['user_delete'] ." then 1 else 0 end as can_delete
      "))->get();

    return $data;
  }

  public static function getUserById($id, $result)
  {
    $q = User::leftJoin('gen_position as gp', function($q){
        $q->on('gp.id', 'position_id')
        ->on('gp.active', DB::raw("'1'"));
      })->leftJoin('gen_group as gg', function($q){
        $q->on('gg.id', 'gp.group_id')
        ->on('gg.active', DB::raw("'1'"));
      })->where('gen_user.active', '1')
      ->where('gen_user.id', $id)
      ->select('gen_user.id as id',
        'position_id',
        'position_name',
        'position_type',
        'gg.id as group_id',
        'group_name',
        'nip',
        'username',
        'full_name',
        'ttd',
        DB::raw("
          case when path_foto is not null then path_foto 
          when path_foto is null and jenis_kelamin = 'Perempuan' then '/upload/photo/woman.png'
          when path_foto is null and jenis_kelamin = 'Laki-laki' then '/upload/photo/man.png'
          end as path_foto
        "),
        'email',
        DB::raw("to_char(ttl, 'yyyy-mm-dd') as ttl"),
        'jenis_kelamin',
        'address',
        'phone')
      ->first();
    if ($q == null){
      $result['state_code'] = 400;
      $result['messages'] = Array(sprintf(trans('messages.dataNotFound'), $id));
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
          'ttl' => $inputs['ttl'] ?? null,
          'jenis_kelamin' => $inputs['jenis_kelamin'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $userLogin
        ]);
        $mode = "Ubah";
      } else {
        $userValid = User::where('active', '1')->where('nip', $inputs['nip'])->first();
        if($userValid){
          throw new exception("nip_duplicate");
        }

        $user = User::create([
          'username' => $inputs['username'],
          'position_id' => $inputs['position_id'] ?? null,
          'nip' => $inputs['nip'],
          'password' => bcrypt($inputs['password']),
          'ttl' => $inputs['ttl'] ?? null,
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
      array_push($result['messages'], sprintf(trans('messages.succesSaveUpdate'),  $mode, $user->username));
    } catch(\Exception $e){
      $log =Array(
        'action' => 'SAV',
        'modul' => 'USER',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $userLogin);
      $result['state_code'] = 500;
      $msg = $e->getMessage() === 'nip_duplicate'
        ? 'NIP sudah terdaftar pada sistem'
        :trans('messages.errorCallAdmin');

      array_push($result['messages'], $msg);
    }

    return $result;
  }

  public static function deleteUserById($id, $result, $loginid)
  {
    try{
      $dsk = DB::table('dis_surat_keluar')->where('active','1')->where('tujuan_user_id', $id)->first();
      $dsm = DB::table('dis_surat_masuk')->where('active','1')->where('to_user_id', $id)->first();
      if($dsk != null || $dsm != null){
        array_push($result['messages'], sprintf(trans('messages.errorDelReferenceUser'), 'User'));
        return $result;
      }

      $user = User::where('active', '1')->where('id', $id)->firstOrFail();

      if ($user->id == 1)
        throw new exception;

      $user->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], sprintf(trans('messages.successDeleting'),  $user->username));
    }catch(\Exception $e){
      $log =Array(
        'action' => 'DEL',
        'modul' => 'USER',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      array_push($result['messages'], trans('messages.errorCallAdmin'));
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
      array_push($result['messages'], sprintf(trans('messages.successUpdatePassword'), $user->username));
    }catch(\Exception $e){
      $log =Array(
        'action' => 'CHPASS',
        'modul' => 'USER',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      array_push($result['messages'], trans('messages.errorCallAdmin'));
    }
    return $result;
  }

  public static function searchUser($respon)
  {
    $q = User::join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active','1')
      ->orderBy('full_name')
      ->select('gen_user.id', DB::raw("full_name || ' - ' || coalesce(position_name,'') as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $q;

    return $respon;
  }

  public static function searchUserSuratKeluar($respon, $loginid)
  {
    $qCek = User::join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active', '1')
      ->where('gen_user.id', $loginid)
      ->where('gp.active', '1')
      ->select(
        'gen_user.id as id', 
        DB::raw("coalesce(gp.parent_id, 0) as parent_id") , 
        'gp.position_name',
        'is_parent',
        'is_admin',
        'is_sekretaris',
        'is_subagumum',
        'is_officer')
      ->first();

    $query = User::join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active','1');

    if($qCek->is_admin){
      $query = $query;
    } else if($qCek->is_sekretaris){
      $query = $query->where('is_subagumum', '1');
    } else if($qCek->is_subagumum){
      $query = $query->where('is_kadin', '1');
    } else if($qCek->is_parent){
      $query = $query->where('is_sekretaris', '1');
    } else if($qCek->parent_id){
      $query = $query->where('gp.id', $qCek->parent_id);
    } else {
      $query = $query;
    }

    $data = $query->select('gen_user.id', DB::raw("full_name || ' - ' || coalesce(position_name,'') as text"))
      ->get();
    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $data;

    return $respon;
  }

  public static function searchUserTtd($respon, $loginid)
  {
    $query = User::leftJoin('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active','1')
      ->whereRaw("(is_kadin = '1' or is_sekretaris = '1')")
      ->select('gen_user.id', DB::raw("full_name || ' - ' || coalesce(position_name,'') as text"))
      ->get();

    $respon['success'] = true;
    $respon['state_code'] = 200;
    $respon['data'] = $query;

    return $respon;
  }

  public static function searchUserSuratMasuk($respon, $loginid)
  {
    $qCek = User::join('gen_position as gp', 'gp.id', 'position_id')
      ->where('gen_user.active', '1')
      ->where('gen_user.id', $loginid)
      ->where('gp.active', '1')
      ->select('gen_user.id as id', 'gp.id as position_id', 'gp.position_name', 'is_sekretaris', 'is_kadin', 'is_admin', 'is_officer', 'is_subagumum')
      ->first();

    if ($qCek != null){
      $query = User::join('gen_position as gp', 'gp.id', 'position_id')->where('gen_user.active','1');
      if($qCek->is_admin){
        $query = $query;
      } else if($qCek->is_kadin){
        $query = $query->where('gp.is_parent', '1');
      } else if($qCek->is_officer || $qCek->is_subagumum){
        $query = $query->whereRaw("is_sekretaris = '1'");
      } else if($qCek->is_sekretaris){
        $query = $query->where('is_kadin', '1')->orWhere('gp.parent_id', $qCek->position_id);
      }else {
        $query = $query->where('gp.parent_id', $qCek->position_id);
      }

      $data = $query->select('gen_user.id', DB::raw("full_name || ' - ' || coalesce(position_name,'') as text"))
        ->get();
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $data;

      return $respon;
    } else {
      $respon['state_code'] = 200;
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Template Surat'));
    }
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
        $pathPoto = Array('path_photo' => $user['path_foto']);
        array_push($respon['data'], $pathPoto);
        array_push($respon['messages'], trans('messages.successUpdatedPhoto'));
      } 
    }catch (\Exception $e){
      $log =Array(
        'action' => 'SAVFT',
        'modul' => 'USER',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($result['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function saveTTD($id, $respon, $inputs, $loginid)
  {
    try{
      $file = Helper::prepareFile($inputs, '/upload/ttd');
      if ($file){
        $user = User::where('active', '1')->where('id', $id)->firstOrFail();

        $user->update([
          'ttd' => '/upload/ttd/'. $file->newName,
          'modified_at' => \Carbon\Carbon::now(),
          'modified_by' => $loginid
        ]);

        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successUpdatedTTD'), ["item" => $user->username]);
      } 
    }catch (\Exception $e){
      $log =Array(
        'action' => 'SAVTTD',
        'modul' => 'USER',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $respon['state_code'] = 500;
      array_push($result['messages'], trans('messages.errorCallAdmin'));
    }
    return $respon;
  }

  public static function createIdTtd($respon, $id, $loginid)
  {
    $cekData = DB::table('gen_user as gu')
      ->where('active', '1')
      ->where('id', $id)
      ->select('username', 'email')
      ->first();
    
    if($cekData != null){
      $info = Array(
        'name' => $cekData->username,
        'email' => $cekData->email
      );
      try{
        $createID = Helper::createCertificate($info);
      
        $respon['success'] = true;
        $respon['state_code'] = 200;
        array_push($respon['messages'], trans('messages.successCreateID'));
      }catch(\Exception $e){
        $log =Array(
          'action' => 'CRTTD',
          'modul' => 'USER',
          'reference_id' => $id ?? 0,
          'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
        );
        $saveLog = ErrorLogRepository::save($log, $loginid);
        $respon['state_code'] = 500;
        array_push($result['messages'], trans('messages.errorCallAdmin'));
      }
    } else {
      $respon['state_code'] = 400;
      $result['messages'] = Array(sprintf(trans('messages.dataNotFound'), 'User'));
    }

    return $respon;
  }
}