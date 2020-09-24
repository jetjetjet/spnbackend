<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\User;

use DB;

class HakAkses
{
  public static $all = array(
    'auditTrail_list',

    'jabatan_list',
    'jabatan_view',
    'jabatan_save',
    'jabatan_delete',
    'jabatan_permission',

    'klasifikasiSurat_list',
    'klasifikasiSurat_view',
    'klasifikasiSurat_save',
    'klasifikasiSurat_delete',

    'nomorSurat_list',

    'sifatSurat_save',
    'sifatSurat_delete',
    
    'suratKeluar_list',
    'suratKeluar_view',
    'suratKeluar_save',
    'suratKeluar_delete',
    'suratKeluar_approve',
    'suratKeluar_verify',
    'suratKeluar_agenda',
    'suratKeluar_sign',
    'suratKeluar_void',

    'suratMasuk_list',
    'suratMasuk_view',
    'suratMasuk_save',
    'suratMasuk_delete',
    'suratMasuk_disposition',
    'suratMasuk_close',
    
    'templateSurat_list',
    'templateSurat_view',
    'templateSurat_save',
    'templateSurat_delete',

    'tipeSurat_save',
    'tipeSurat_delete',

    'unit_list',
    'unit_view',
    'unit_save',
    'unit_delete',

    'user_list',
    'user_view',
    'user_save',
    'user_delete',
  );

  public static function all()
  {
    $result = array();
    foreach (self::$all as $value){
      $values = explode('_', $value);
      if (!isset($result[$values[0]])){
        $result[$values[0]] = new \stdClass();
        $result[$values[0]]->module = $values[0];
        $result[$values[0]]->actions = array();
      }
      
      $action = new \stdClass();
      $action->value = $value;
      $action->text = $values[1];
      array_push($result[$values[0]]->actions, $action);
    }
    ksort($result);
    return $result;
  }

  public static function arrAll()
  {
    $perms = self::all();
    $result = array();
    foreach($perms as $val){
      array_push($result, $val);
    }
    return $result; 
  }
}