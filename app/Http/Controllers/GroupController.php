<?php

namespace App\Http\Controllers;

use App\Model\Group;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

class GroupController extends Controller
{
  public function getAll(Request $request)
  {
    $result = Helper::$responses;
    $q = Group::where('active', '1')->get();
    $result['state_code'] = 200;
    $result['success'] = true;
    $result['data'] = $q;

    return response()->json($result, 200);
  }

  public function getById(Request $request, $id = null)
  {
    $result = Helper::$responses;
    dd($result);
    $q = Group::where('active', '1')
      ->where('gen_group.id', $id)
      ->first();
    if ($q == null){
      $result['state_code'] = 400;
      $result['messages'] = trans('messages.dataNotFound', ["item" => $id]);
    } else {
      $result['state_code'] = 200;
      $result['success'] = true;
      $result['data'] = $q;
    }
    return response()->json($result, $result['state_code']);
  }

  public function save(Request $request, $id = null)
  {
    $rules = array(
      'group_name' => 'required',
      'group_code' => 'required'
    );

    $inputs = $request->all();
    $validator = Validator::make($inputs, $rules);

    if ($validator->fails()){
      $result['state_code'] = 400;
      $result['messages'] = $validator->messages();
      $result['data'] = $inputs;
      return response()->json($result, 400);
    }

    $result = Helper::$responses;
    try{
      $userLogin = Auth::user()->getAuthIdentifier();
      $jabatan = null;
      $mode = "";
      if ($id){
        $jabatan = Group::where('active', '1')->where('id', $id)->firstOrFail();

        $jabatan->update([
          'group_name' => $inputs['group_name'],
          'group_code' => $inputs['group_code'],
          'modified_at' => DB::raw('now()'),
          'modified_by' => $userLogin
        ]);
        $mode = "Ubah";
      } else {
        $jabatan = Group::create([
          'group_name' => $inputs['group_name'],
          'group_code' => $inputs['group_code'],
          'active' => '1',
          'created_at' => DB::raw('now()'),
          'created_by' => $userLogin
        ]);
        $mode = "Simpan";
      }
      $result['success'] = true;
      $result['state_code'] = 200;
      $result['data'] = $jabatan;
      array_push($result['messages'], trans('messages.succesSaveUpdate', ["item" => $jabatan->group_code, "item2" => $mode]));
      return response()->json($result, 200);
    } catch(\Exception $e){
      $result['state_code'] = 500;
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }

  public function delete(Request $request, $id = null)
  {
    $result = Helper::$responses;
    try{
      $jabatan = Group::where('active', '1')->where('id', $id)->firstOrFail();

      $jabatan->update([
        'active' => '0',
        'modified_at' => \Carbon\Carbon::now(),
        'modified_by' => Auth::user()->getAuthIdentifier()
      ]);

      $result['success'] = true;
      $result['state_code'] = 200;
      array_push($result['messages'], trans('messages.successDeleting', ["item" => $jabatan->group_code]));
      return response()->json($result, 200);
    }catch(\Exception $e){
      array_push($result['messages'], $e->getMessage());
      return response()->json($result, 500);
    }
  }
}
