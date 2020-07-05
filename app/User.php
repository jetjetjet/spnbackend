<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
//use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;
use DB;

class User extends Authenticatable
{
    protected $table = 'gen_user';
    use HasApiTokens, Notifiable;

    public $timestamps = false;
    protected $hidden = ['password', 'active', 'ttd'];
    protected $fillable = [
      'username'
      ,'nip'
      ,'position_id'
      ,'email'
      ,'password'
      ,'full_name'
      ,'ttl'
      ,'ttd'
      ,'jenis_kelamin'
      ,'path_foto'
      ,'password'
      ,'phone'
      ,'address'
      ,'active'
      ,'created_at'
      ,'created_by'
      ,'modified_at'
      ,'modified_by'
  ];

  public function getAuthIdentifierName()
  {
    return $this->attributes['username'];
  }

  public function getAuthIdentifier()
  {
    return $this->attributes['id'];
  }

  public function routeNotificationForMail()
  {
    return $this->email;
  }

  // public function getEmail()
  // {
  //   return $this->attributes['email'];
  // }

  public function getAuthPassword()
  {
      // Returns the (hashed) password for the user
  }

  public function getRememberToken()
  {
      // Return the token used for the "remember me" functionality
  }

  public function setRememberToken($value)
  {
      // Store a new token user for the "remember me" functionality
  }

  public function getRememberTokenName()
  {
      // Return the name of the column / attribute used to store the "remember me" token
  }

  public function scopeGetPermission($query, $id)
  {
    $perm = [];
    $permissions = $query->join('gen_position as gp', 'gp.id', 'position_id')
    ->join('gen_positionmenu as gpm', 'gp.id', 'gpm.position_id')
    ->where([
      'gp.active' => '1',
      'gpm.active' => '1',
      'gen_user.active' => '1',
      'gen_user.id' => $id,
    ])
    ->where('permissions', '!=', "")
    ->select(DB::raw('string_agg(permissions, \',\') as permissions'))->first();

    if($permissions->permissions != null)
      $perm = explode(",",$permissions->permissions);
      
    return $perm;
  }

  public function scopeJabatanGroup($query, $idUser)
  {
    return self::getJabatanGroup($query)
    ->where('gen_user.id', $idUser)
    ->select('gp.id as position_id', 'position_type', 'position_name', 'gg.id as group_id', 'group_name');
  }

  public function scopeCheckAdmin($query, $idUser)
  {
    $q = self::getJabatanGroup($query)
      ->where('gen_user.id', $idUser)
      ->where('gg.id', 1)
      ->select(db::raw("1 as col"))
      ->first();
    return isset($q->col) ? $q->col : 0 ;
  }

  private static function getJabatanGroup($query)
  {
    return $query->leftJoin('gen_position as gp', function($q){
      $q->on('gp.id', 'position_id')
      ->on('gp.active', DB::raw("'1'"));
    })->leftJoin('gen_group as gg', function($q){
      $q->on('gg.id', 'gp.group_id')
      ->on('gg.active', DB::raw("'1'"));
    })
    ->where('gen_user.active', '1');
  }

}
