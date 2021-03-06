<?php

namespace app\Http\Repositories;

use App\Model\TemplateSurat;
use App\Model\DetailTemplate;
use App\Http\Repositories\ErrorLogRepository;
use App\Model\File;
use App\Helpers\Helper;
use DB;
use Exception;

class TemplateSuratRepository
{
  public static function getList($filter, $perm)
  {
    $data = Array();

    $q = TemplateSurat::where('active', '1')
      ->select('id', 'template_type', 'template_name',
      DB::raw("
        case when 1 = ". $perm['templateSurat_edit'] ." then 1 else 0 end as can_edit
      "),
      DB::raw("
        case when 1 = ". $perm['templateSurat_delete'] ." then 1 else 0 end as can_delete
      "))
      ->get();
    
    foreach($q as $qx){
      $tmp = new \stdClass();
      $detail = DB::table('gen_detailtemplate as gdt')
        ->join('gen_file as gf', 'gf.id', 'file_id')
        ->where('gdt.active', '1')
        ->where('template_id', $qx->id)
        ->select('original_name', 'file_path')
        ->get();

      $tmp = $qx;
      $tmp->detail = $detail;
      array_push($data, $tmp);
    }

    return $data;
  }

  public static function getById($respon, $id)
  {
    $tmp = new \stdClass();
    $q = TemplateSurat::where('active', '1')
      ->where('id', $id)
      ->select('id', 'template_type', 'template_name')
      ->first();
    if($q != null){
      $detail = DB::table('gen_detailtemplate as gdt')
        ->join('gen_file as gf', 'gf.id', 'file_id')
        ->where('gdt.active', '1')
        ->where('template_id', $q->id)
        ->select('gdt.id as id','file_id', DB::raw('original_name as name'), 'file_path')
        ->get();
      $tmp = $q;
      $tmp->detail = $detail;
      $respon['success'] = true;
      $respon['state_code'] = 200;
      $respon['data'] = $tmp;
    } else {
      $respon['state_code'] = 400;
      array_push($respon['messages'], sprintf(trans('messages.dataNotFound'),'Template Surat'));
    }

    return $respon;
  }

  public static function save($id, $result,$inputs, $loginid)
  {
    try{
      DB::transaction(function () use (&$result, $id, $inputs, $loginid){
        $valid = self::saveTemplate($result, $id, $inputs, $loginid);
        if (!$valid) return $result;

        if($id != null){
          $valid = self::removeMissingTemplate($result, $id, $inputs, $loginid);
        }

        $valid = self::saveDetailTemplate($result, $id, $inputs, $loginid);
        if (!$valid) return $result;

        $result['success'] = true;
        $result['state_code'] = 200;
        array_push($result['messages'], trans('messages.successSaveTemplate'));
        $inputs['id'] = $result['id'];
        unset($result['id']);
        //$result['data'] = $inputs;
      });
    } catch (\Exception $e) {
      $log =Array(
        'action' => 'SAV',
        'modul' => 'TEMPSURAT',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $result['state_code'] = 500;
      array_push($result['messages'], trans('messages.errorSaveTemplate'));
    }
    return $result;
  }

  public static function saveTemplate(&$result, $id, $inputs, $loginid)
  {
    if($id){
      $update = TemplateSurat::where('id', $id)->where('active', '1')->first();
      $update->update([
        'template_type' => $inputs['template_type'] ?? null,
        'template_name' => $inputs['template_name'] ?? null,
        'modified_at' => DB::raw('now()'),
        'modified_by' => $loginid
      ]);
      $result['id'] = $update->id ?: $id;
      return true;
    } else {
      $save = TemplateSurat::create([
        'template_type' => $inputs['template_type'] ?? null,
        'template_name' => $inputs['template_name'] ?? null,
        'active' => '1',
        'created_at' => DB::raw('now()'),
        'created_by' => $loginid
      ]);
      $result['id'] = $save->id ?: $id;
      return true;
    }
  }

  public static function saveDetailTemplate(&$result, $id, $inputs, $loginid)
  {
    if(isset($inputs["file"])){
      $files = $inputs['file'];
      $id = isset($id) ? $id : $result['id'];
      foreach($files as $file){
        $prepareFile = Helper::prepFile($file, '/upload/templatesurat');
        $newFile = File::create([
          'file_name' => $prepareFile->newName,
          'file_path' => '/upload/templatesurat/'.$prepareFile->newName,
          'original_name' => $prepareFile->originalName,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);
        $saveDetail = DetailTemplate::create([
          'template_id' => $id,
          'file_id' => $newFile->id,
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $loginid
        ]);
      }
    }
    return true;
  }

  public static function removeMissingTemplate(&$result, $id, $inputs, $loginid)
  {
    if(isset($inputs['existing_file'])){
      $fileId = array_map(function($item) { return $item->id; }, json_decode($inputs['existing_file']));
      $data = DetailTemplate::where('active', '1')
      ->where('template_id', $id)
      ->whereNotIn('id', $fileId)
      ->update([
        'active' => '0',
        'modified_by' => $loginid,
        'modified_at' => now()->toDateTimeString()
        ]);
    }
    
    return true;
  }

  public static function delete($result, $id, $loginid)
  {
    try{
      $template = TemplateSurat::where('active', '1')->where('id', $id)->firstOrFail();

      $template->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => $loginid
      ]);

      $detail = DetailTemplate::where('active', '1')
        ->where('template_id', $id)
        ->update([
          'active' => '0',
          'modified_at' => \Carbon\Carbon::now(),
          'modified_by' => $loginid
        ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], sprintf(trans('messages.successDeleting'), 'Template surat'));
    }catch(\Exception $e){
      $log =Array(
        'action' => 'DEL',
        'modul' => 'TEMPSURAT',
        'reference_id' => $id ?? 0,
        'errorlog' => $e->getMessage() ?? 'NOT_RECORDED'
      );
      $saveLog = ErrorLogRepository::save($log, $loginid);
      $result['state_code'] = 500;
      array_push($result['messages'], sprintf(trans('messages.errorDeleting'), 'Template surat'));
    }
    return $result;
  }
}
