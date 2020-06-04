<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use DB;

class User extends Authenticatable
{
    protected $table = 'gen_user';
    use HasApiTokens, Notifiable;

    public $timestamps = false;
    protected $fillable = [
      'username'
      ,'email'
      ,'password'
      ,'full_name'
      ,'password'
      ,'phone'
      ,'address'
      ,'active'
      ,'created_at'
      ,'created_by'
      ,'modified_at'
      ,'modified_by'
  ];

  protected $hidden = ['password', 'active'];

  public function getAuthIdentifierName()
  {
    return $this->attributes['username'];
  }

  public function getAuthIdentifier()
  {
    return $this->attributes['id'];
  }

  public function getEmail()
  {
    return $this->attributes['email'];
  }

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
    $permissions = $query->join('gen_groupmenu as gg', 'gg.group_id', 'gen_user.group_id')
    ->where([
      'gg.active' => '1',
      'gen_user.active' => '1',
      'gen_user.id' => $id,
    ])
    ->where('permissions', '!=', "")
    ->select(DB::raw('string_agg(permissions, \'|\') as permissions'))->first();

    $perm = explode("|",$permissions->permissions);
    return $perm;
  }

}
