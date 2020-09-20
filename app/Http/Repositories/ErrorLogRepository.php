<?php
namespace app\Http\Repositories;

use App\Model\GenErrorlog;
use DB;
use Exception;

class ErrorLogRepository
{
  public static function save($log, $loginid)
  {
    try{
      $log = GenErrorlog::create([
        'action' => $log['action'],
        'modul' => $log['modul'],
        'reference_id' => $log['reference_id'],
        'errorlog' => $log['errorlog'],
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
    } catch(\Exception $e){
      // lewat
    }
    return true;
  }
}