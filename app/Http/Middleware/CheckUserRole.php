<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role = 1)
    {
        if($request->user()->user_role != $role) {
            return response()->json(["code" => 0, "message" => "You have not permission to access this."]);
        }

        return $next($request);
    }
}
