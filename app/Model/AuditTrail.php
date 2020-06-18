<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
  protected $table = 'gen_audit_trail';
  public $timestamps = false;
  protected $fillable = [
    'path',
    'action',
    'modul',
    'mode',
    'success',
    'messages',
    'created_at',
    'created_by'
  ];
}