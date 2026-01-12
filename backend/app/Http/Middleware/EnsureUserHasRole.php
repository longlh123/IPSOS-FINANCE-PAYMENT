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
    public function handle(Request $request, Closure $next, ... $roles): Response
    {
        //Check if the user is authenticated
        if(!Auth::check())
        {
            return response()->json([
                'status_code' => 401, 
                'message' => 'Unauthorized - please log in.']
            , 401);
        }

        $user = Auth::user();

        // Log::info("Permission Roles: " . implode(", ", $roles));
        
        // if ($user && $user->userDetails) {
        //     Log::info('User Role: ' . $user->userDetails->role->name);
        // } else {
        //     Log::warning('UserDetail not found for user ID: ' . $user?->id);
        // }

        if(!$user->userDetails || !$user->userDetails->hasAnyRole($roles))
        {
            return response()->json([
                'status_code' => 403, 
                'message' => 'You do not have permission to access this resource.']
            , 403);
        }

        return $next($request);
    }
}
