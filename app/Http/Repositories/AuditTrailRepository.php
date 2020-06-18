<?php

namespace app\Http\Repositories;

use App\Model\AuditTrail;
use DB;
use Exception;

class AuditTrailRepository
{
  public static function saveAuditTrail($request, $result, $action, $loginid)
  {
    try{
    $mod = explode("/", $request->path());
      AuditTrail::create([
        'path' => $request->path(),
        'action' => $action,
        'modul' => $mod[1],
        'success' => $result['success'],
        'messages' => $result['debugMessages'] ?? null,
        'created_by' => $loginid,
        'created_at' => DB::raw("now()")
      ]);
    } catch(Exception $e)
    {
      //lewaaat
    }
  }
}