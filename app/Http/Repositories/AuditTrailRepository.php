<?php

namespace app\Http\Repositories;

use App\Model\AuditTrail;
use DB;
use Exception;

class AuditTrailRepository
{
  public static function getAll($param)
  {
    $q = DB::table('gen_audit_trail as gat')
      ->leftJoin('gen_user as cr', 'cr.id', 'gat.created_by')
      ->leftJoin('gen_position as gp', 'gp.id', 'cr.position_id')
      ->select('gat.id', 
        'path',
        'action',
        'modul',
        'success',
        'messages',
        DB::raw("case when cr.full_name is null 
          then 'User tidak ada'
          else cr.full_name || ' - ' || gp.position_name end as created_by"),
        'gat.created_at'
      );

    $q = $param['order'] != null
      ? $q->orderByRaw("gat.". $param['order'])
      : $q->orderBy('gat.created_at', 'DESC');

    $q = $param['filter'] != null 
      ? $q->whereRaw("gat.".$param['filter']. " like ? ", ['%' . trim($param['q']) . '%' ])
      : $q;

    $data = $q->paginate($param['per_page']);;

    return $data;
  }

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