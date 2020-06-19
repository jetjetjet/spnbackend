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
      $responses = array( 'state_code' => "", 'success' => false, 'messages' => "", 'data' => Array());
      $user = Auth::user();
      if (!$request->user()->tokenCan($actions[0])) 
      {
        $responses['messages'] = "You are not authorized to access this resource / execute the action!";
        $responses['state_code'] = 401;
        return response()->json($responses, 401);
      }
      
      return $next($request);
    }
}
