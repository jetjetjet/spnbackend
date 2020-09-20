<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GenErrorlog extends Model
{
    protected $table = 'gen_errorlog';
    public $timestamps = false;
    protected $fillable = ['action',
      'modul',
      'errorlog',
      'reference_id',
      'created_at',
      'created_by'
    ];
}