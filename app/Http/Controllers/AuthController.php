<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Hash;
use App\User;
use App\Helpers\Helper;
use Session;
use Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
      $result = Helper::$responses;
      $rules = array(
        'email' => 'required|email',
        'password' => 'required',
      );
  
      $inputs = $request->all();
      $validator = Validator::make($inputs, $rules);
      if ($validator->fails()){
        $result['state_code'] = 400;
        $result['messages'] = $validator->messages();
        $result['data'] = $inputs;
        return response()->json($result, 400);
      }

      $user = User::where('email', $request->email)->first();
      if($user != null){
        if (!Hash::check($request->password, $user->password)) {
          array_push($result['messages'],'Password Anda Salah');
          $result['state_code'] = 400;
        } else {
          $perm = User::getPermission($user->id);
          $sa = User::checkAdmin($user->id);

          if($sa)
            array_push($perm, 'is_admin');
            
          $token = $user->createToken($request->email, $perm);
          $jbtgrp = User::JabatanGroup($user->id)->first();
          $data = Array( "token" => $token->plainTextToken,
            "userid" => $user->id,
            "username" => $user->username,
            "email" => $user->email,
            "full_name" => $user->full_name,
            "nip" => $user->nip,
            "position_id" => $jbtgrp->position_id ?? null,
            "position_name" => $jbtgrp->position_name ?? null,
            "group_id" => $jbtgrp->group_id ?? null,
            "group_name" => $jbtgrp->group_name ?? null,
            "path_foto" => $user->path_foto,
            "address" => $user->address,
            "phone" => $user->phone,
            "jenis_kelamin" => $user->jenis_kelamin,
            "ttl" => $user->ttl
          );
          $result['success'] = true;
          $result['state_code'] = 200;
          $result['data'] = $data;
        }
      } else {
        array_push($result['messages'],'Email anda salah atau tidak terdaftar');
        $result['state_code'] = 400;
      }
      return response()->json($result, $result['state_code']);
    }

    public function logout(Request $request)
    {
      $user = User::where('email', Auth::user()->getEmail())->first();
      $user->tokens()->where('name', Auth::user()->getEmail())->delete();
      return response()->json(['success' => true], 200);
    }
}
