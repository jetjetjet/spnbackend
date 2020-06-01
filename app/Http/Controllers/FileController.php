<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
  public static function prepareFiles($inputs, $subFolder=''){
    $files = array();
    try {
      $fileCount = isset($inputs['plupload_count']) ? $inputs['plupload_count'] : 0;
      for ($i = 0; $i < $fileCount; $i++){   
        if (!isset($inputs['plupload_' . $i . '_status'])) continue;
        $status = $inputs['plupload_' . $i . '_status'];
        if ($status !== 'done') continue;

        $file = new \stdClass();
        if (!isset($inputs['plupload_' . $i . '_tmpname'])) continue;
        $file->path = $inputs['plupload_' . $i . '_tmpname'];
        if (!isset($inputs['plupload_' . $i . '_name'])) continue;
        $file->name = $inputs['plupload_' . $i . '_name'];
        array_push($files, $file);

        // Moves from tmp to parent.
        $oldPath = base_path() . '/upload/files/tmp/' . $file->path;
        $newPath = base_path() . '/upload/files/' . $subFolder . $file->path;
        rename($oldPath, $newPath);

        //update file->path
        $file->path = $subFolder . $file->path;
      }
    } catch (Exception $e){
        // supress
    }

    return $files;
  }
}
