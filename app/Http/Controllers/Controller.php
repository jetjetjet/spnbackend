<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  protected function mapRows($rows)
  {
    $items = array();
    if (!is_array($rows)) return $items;            

    // Validates all array property has same length of array.
    $lengths = array();
    $columns = array();
    foreach ($rows as $key => $value){
      if (!is_array($rows[$key])) continue;            
      array_push($columns, $key);  
      array_push($lengths, count($rows[$key]));  
    }

    // At least 1 array property and should not have different length.
    $lengths = array_unique($lengths);
    if (count($lengths) !== 1) return $items;

      // Loops row.
    $keys = array_keys($rows[$columns[0]]);
    $rowIndex = 1;
    foreach ($keys as $i){
      $item = new \stdClass();
      $item->rowIndex = $rowIndex++;

      // Loops column.
      $allEmpty = true;
      foreach ($columns as $column){
        $item->{$column} = $rows[$column][$i];
        $allEmpty = $allEmpty && empty($item->{$column}); 
      }

      // Skips if all empty.
      if ($allEmpty) continue;
      array_push($items, $item);
    }

    return $items;
  }

  protected function mapRowsX($rows)
  {
    $items = array();
    if (!is_array($rows) || empty($rows)) return $items;

    $first = reset($rows);
    if (is_scalar($first)){
      return $rows;
    }

    // Loops row.
    $rowIndex = 1;
    foreach ($rows as $row){
      $item = (object)$row;
      $item->rowIndex = $rowIndex++;

      foreach ($item as $key => $value){
        if (!is_array($value)) continue;

        // Loops revursively.
        $item->{$key} = self::mapRowsX($value);
      }
      
      array_push($items, $item);
    }

    return $items;
  }
}
