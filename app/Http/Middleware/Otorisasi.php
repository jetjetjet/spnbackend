<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Otorisasi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$actions)
    {
      $user = Auth::user();

      if($request->user()->tokenCan('is_admin')){
        return $next($request);
      }

      if (!$request->user()->tokenCan($actions[0])) 
      {
        $msg = Array("message" =>"Tidak dapat memproses.");
        return response()->json($msg, 400);
      }
      
      return $next($request);
    }
}
