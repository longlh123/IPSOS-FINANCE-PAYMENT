<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ... $role): Response
    {
        //Check if the user is authenticated
        if(!Auth::check())
        {
            return response()->json(['status_code' => 401, 'message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        if(!$user->userDetails->hasRole($role))
        {
            return response()->json(['status_code' => 401, 'message' => 'You do not have the required role to access this project.'], 401);
        }

        return $next($request);
    }
}
