<?php
namespace App\Helpers;

class Helper
{
  public static $responses = array( 'state_code' => "", 'success' => false, 'messages' => array(), 'data' => Array());

  public static function mapFilter($req){
    $filter = new \stdClass();

    $filter->limit = $req->input('take') !== null ? $req->input('take') : 10 ;
    $filter->offset = $req->input('skip') !== null ? $req->input('skip') : 0;

    // Sort columns.
    $filter->sortColumns = array();
    $orderColumns = $req->input('sort') != null ? $req->input('sort') : array();
    if ($orderColumns){
        $orderParse = json_decode($orderColumns, true);
        $filterColumn = new \stdClass();
        $filterColumn->column = $orderParse[0]['selector'];
        $filterColumn->order = $orderParse[0]['desc'] == true ? 'DESC' : 'ASC';
        array_push($filter->sortColumns, $filterColumn);
    }

    //Search Column
    $filter->search = $req->input('filter') != null ? json_decode($req->input('filter'), true) : array();
    
    return $filter;
  }

  public static function prepareFile($inputs, $subFolder)
  {
    $file = new \StdClass;
    try {
      $file = isset($inputs['file']) ? $inputs['file'] : null;
      $file->path = base_path() . $subFolder;
      $file->newName = time()."_".$file->getClientOriginalName();
      $file->originalName = $file->getClientOriginalName();
      $file->move($file->path ,$file->newName);
    } catch (Exception $e){
        // supress
    }
    return $file;
  }
}