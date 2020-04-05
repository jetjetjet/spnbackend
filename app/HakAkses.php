<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use App\User;

use DB;

class HakAkses
{
  public static $all = array(
    'menu_view',
    'menu_save',
    'menu_edit',
    'menu_delete',
    'user_view',
    'user_save',
    'user_edit',
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
}