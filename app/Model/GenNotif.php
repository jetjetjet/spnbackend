<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GenNotif extends Model
{
  protected $table = 'gen_notif';
  public $timestamps = false;
  protected $fillable = ['reference_id',
    'type',
    'to_user_id',
    'url',
    'display',
    'description',
    'active',
    'parent_id',
    'index',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
