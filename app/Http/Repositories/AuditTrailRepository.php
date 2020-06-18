<?php

namespace app\Http\Repositories;

use App\Model\AuditTrail;
use DB;
use Exception;

class AuditTrailRepository
{
  public static function getAll($filter)
  {
    $data = new \stdClass();
    $q = DB::table('gen_audit_trail as gat')
      ->leftJoin('gen_user as gu', 'gu.id', 'gat.created_by');
    
    if($filter->search){
			foreach($filter->search as $qCol){
				$sCol = explode('|', $qCol);
				$fCol = str_replace('"', '', $sCol[0]);
				$q = $q->where($sCol[0], 'like', '%'.$sCol[1].'%');
			}
    }
    
    $qCount = $q->count();

    if ($filter->sortColumns){
			$order = $filter->sortColumns[0];
			$q = $q->orderBy($order->column, $order->order);
		} else {
			$q = $q->orderBy('gat.id', 'DESC');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select('gat.id', 
      DB::raw("coalesce(gu.full_name, 'Blank') as full_name"),
      'path',
      'action',
      'modul',
      'success',
      'messages',
      'created_at'
      )->get();

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