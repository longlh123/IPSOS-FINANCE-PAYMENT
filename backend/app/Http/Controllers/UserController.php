<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\UserDetail;

class UserController extends Controller
{
    public function metadata(Request $request)
    {
        try {
            $roles = Role::query()
                ->select('id', 'name', 'department_id')
                ->orderBy('name', 'asc')
                ->get();

            $departments = Department::query()
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'Successfully',
                'data' => [
                    'roles' => $roles,
                    'departments' => $departments,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function index(Request $request)
    {
        try
        {
            $users = User::query()
                ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                ->leftJoin('roles', 'user_details.role_id', '=', 'roles.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'user_details.first_name',
                    'user_details.last_name',
                    'roles.name as role'
                )
                ->orderBy('users.id', 'asc')
                ->get();

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'Successfully',
                'data' => $users
            ], Response::HTTP_OK);
        } catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        try {
            $role = Role::query()->findOrFail($validated['role_id']);

            if ((int) $role->department_id !== (int) $validated['department_id']) {
                return response()->json([
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Role does not belong to selected department.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = DB::transaction(function () use ($validated) {
                $createdUser = User::query()->create([
                    'name' => $validated['email'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                ]);

                UserDetail::query()->create([
                    'user_id' => $createdUser->id,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'role_id' => $validated['role_id'],
                    'department_id' => $validated['department_id'],
                ]);

                return $createdUser;
            });

            return response()->json([
                'status_code' => Response::HTTP_CREATED,
                'message' => 'User created successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
