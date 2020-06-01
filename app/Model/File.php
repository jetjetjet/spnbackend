<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
  protected $table = 'gen_file';
  public $timestamps = false;
  protected $fillable = ['file_name',
    'file_path',
    'original_name',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];
}
