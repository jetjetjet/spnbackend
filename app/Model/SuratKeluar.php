<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\DisSuratKeluar;
use DB;
use Auth;

class SuratKeluar extends Model
{
  protected $table = 'surat_keluar';
  public $timestamps = false;
  protected $fillable = [
    'nomor_agenda',
    'nomor_surat',
    'jenis_surat',
    'tgl_surat',
    'klasifikasi_id',
    'sifat_surat',
    'tujuan_surat',
    'hal_surat',
    'to_user',
    'lampiran_surat',
    'approval_user',
    
    'approved_by',
    'approved_at',
    'is_approved',
    
    'is_disposition',
    'disposition_by',
    'disposition_at',
    
    'is_agenda',
    'agenda_by',
    'agenda_at',

    'file_id',
    'active',
    'created_at',
    'created_by',
    'modified_at',
    'modified_by'
  ];

  public function scopeGetAll($query, $filter, $loginId)
  {
    $data = new \stdClass();
    $q = $query
      ->where([
        'gg.active' => '1',
        'gen_user.active' => '1'
      ]);
    
    if(!$admin){
      $q = $q->where('created_by', $loginId);
    }

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
			$q = $q->orderBy('gen_user.created_at');
    }
    
		$q = $q->skip($filter->offset);
    $q = $q->take($filter->limit);
    
    $data->totalCount = $qCount;
    $data->data = $q->select('gen_user.id', 
      'gg.group_name',
      'gg.id as group_id',
      'username', 
      'full_name',
      'email',
      'phone',
      'address',
      'last_login'
      )->get();

    return $data;
  }

}
