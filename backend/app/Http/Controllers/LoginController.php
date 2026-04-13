<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\UserDetailResource;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        // Validate the login request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        
        // Attempt to authenticate the user
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        
        if(!Auth::attempt($credentials)) 
        {
            // Return an error response if authentication fails
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED, //401
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Retrieve the authenticated user
        $user = Auth::user();
        
        // Optionally, you can check if the user is authenticated
        if (!$user) {
            return response()->json([
                'status_code' => Response::HTTP_UNAUTHORIZED, //401
                'message' => 'Unauthenticated',
            ]);
        }

        if (!Hash::check($request->password, $user->password, [])) {
            throw new \Exception('Error in Login 1');
        }

        // Generate a token for the authenticated user
        $tokenResult = $user->createToken($user->name)->plainTextToken;
        
        //$user = User::where('email', $request->email)->first();
        
        // Specify the columns you want to return
        // $userData = [
        //     'id' => $user->id,
        //     'name' => $user->name,
        //     'email' => $user->email,
        // ];
        
        //Specify the columns you want to return
        //$userData = $user->only(['id', 'name', 'email']);

        return response()->json([
            'status_code' => Response::HTTP_OK, //200,
            'message' => 'Login successfully',
            'token' => $tokenResult,
            'user' => new UserDetailResource($user),
            // 'user' => $userData,
            // 'role' => $user->roles()->pluck('name'),
            // 'token' => $tokenResult
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        //Revoke the token that used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
