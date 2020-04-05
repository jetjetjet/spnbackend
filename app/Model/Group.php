<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'gen_group';
    public $timestamps = false;
    protected $fillable = ['unit_id',
      'group_code',
      'group_name',
      'active',
      'created_at',
      'created_by',
      'modified_at',
      'modified_by'
    ];
}
