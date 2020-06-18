<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use Hash;
use DB;
use Notification;
use App\User;
use App\Helpers\Helper;
use Session;
use Validator;
use App\Notifications\CustomResetPasswordNotification;
use App\Http\Repositories\AuditTrailRepository;

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
    $idUser = $user->id ?? 0;
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Login', $idUser);

    return response()->json($result, $result['state_code']);
  }

  public function logout(Request $request)
  {
    //$user = User::where('email', Auth::user()->getEmail())->first();
    $user = request()->user();
    $idUser = $user->id ?? 0;
    $result = Helper::$responses;
    $result['success'] = true;
    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'Logout', $idUser);
    $user->tokens()->where('name', $user->email)->delete();
    return response()->json(['success' => true], 200);
  }

  public function forgotPassword(Request $request)
  {
    $result = Helper::$responses;
    $input = $request->all();
    $rules = array(
        'email' => "required|email",
    );

    $validator = Validator::make($input, $rules);
    if ($validator->fails()) {
      $result['state_code'] = 500;
      $result['messages'] = $validator->messages();
      $result['data'] = $inputs;
    } else { 
      DB::table('password_resets')->insert([
        'email' => $input['email'],
        'token' => $this->generateToken(),
        'created_at' => DB::raw("now()")
      ]);

      //Get the token just created above
      $tokenData = DB::table('password_resets')
      ->where('email', $input['email'])->first();

      if ($this->sendResetEmail($input['email'], $tokenData->token)) {
        $result['state_code'] = 200;
        $result['success'] = true;
        array_push($result['messages'],trans('A reset link has been sent to your email address.'));
      } else {
        $result['state_code'] = 500;
        array_push($result['messages'],['error' => trans('A Network Error occurred. Please try again.')]);
      }
    }
    
    return response()->json($result, $result['state_code']);
  }

  public function resetPassword(Request $request)
  {
    $result = Helper::$responses;
    $idUser = 0;
    $input = $request->all();
    $rules = array(
        'email' => "required|email",
        'new_password' => "required",
        'konci_pas' => "required"
    );

    $validator = Validator::make($input, $rules);
    if ($validator->fails()) {
      $result['state_code'] = 500;
      $result['messages'] = $validator->messages();
      $result['data'] = $input;
    }

    $getTokenReset = DB::table('password_resets')
      ->where('email', $input['email'])
      ->orderBy('created_at', 'DESC')
      ->select('token')
      ->first();

    if($getTokenReset->token == $input['konci_pas']){
      $rPass = User::where('email', $input['email'])->where('active', '1')->first();
      $rPass->update([
        'password' => bcrypt($input['new_password']),
        'modified_at' => DB::raw('now()'),
        'modified_by' => $rPass->id
      ]);

      if($rPass != null){
        DB::table('password_resets')->where('email', $input['email'])->delete();
      }
      
      $result['state_code'] = 200;
      $result['success'] = true;
      array_push($result['messages'], trans('messages.successResetPassword'));
      $idUser = $rPass->id;
    } else {
      $result['state_code'] = 500;
      array_push($result['messages'], trans('messages.failResetPassword'));
      $result['data'] = $input;
    }

    $audit = AuditTrailRepository::saveAuditTrail($request, $result, 'ResetPassword', $idUser);
    return response()->json($result, $result['state_code']);
  }

  private function sendResetEmail($email, $token)
  {
    $link = config('base_url') . 'password/reset?konci=' . $token . '&email=' . urlencode($email);
    $details = [
      'body' => 'Reset Password',
      'thanks' => 'Admin',
      'actionText' => 'Reset Password',
      'actionURL' => $link
  ];
    try {
      User::where('email', $email)->first()->notify(new CustomResetPasswordNotification($details));
        return true;
    } catch (\Exception $e) {
        return false;
    }
  }

  private function generateToken()
  {
      $key = config('app.key');
      // Illuminate\Support\Str;
      if (Str::startsWith($key, 'base64:')) {
          $key = base64_decode(substr($key, 7));
      }
      return hash_hmac('sha256', Str::random(40), $key);
  }

}
