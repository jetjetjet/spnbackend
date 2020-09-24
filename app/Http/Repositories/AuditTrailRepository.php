<?php

namespace app\Http\Repositories;

use App\Model\AuditTrail;
use DB;
use Exception;

class AuditTrailRepository
{
  public static function getAll($filter)
  {
    $q = DB::table('gen_audit_trail as gat')
      ->join('gen_user as cr', 'cr.id', 'gat.created_by')
      ->leftJoin('gen_user as md', 'md.id', 'gat.modified_by')
      ->select('gat.id', 
        'path',
        'action',
        'modul',
        'success',
        'messages',
        'created_at',
        DB::raw("coalesce(cr.full_name, '-') as created_by"),
        'created_at',
        DB::raw("coalesce(cr.full_name, '-') as modified_by"),
        'modified_at'
      );

    $q = $param['order'] != null
      ? $q->orderByRaw("gat.". $param['order'])
      : $q->orderBy('gat.created_at', 'DESC');

    $q = $param['filter'] != null 
      ? $q->whereRaw("gat.".$param['filter']. " like ? ", ['%' . trim($param['q']) . '%' ])
      : $q;

    $data = $q->paginate($param['per_page']);;

    return $q;
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