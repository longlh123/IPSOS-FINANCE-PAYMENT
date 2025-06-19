<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Department;
use App\Models\Team;
use App\Http\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        try{
            $query = Department::with('teams');
            $departments = $query->get();

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of project types requested successfully',
                'data' => DepartmentResource::collection($departments) 
            ], Response::HTTP_OK);

        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function get_teams($departmentId)
    {
        try{
            $teams = Team::where('department_id', $departmentId)->pluck('name');
            
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of project types requested successfully',
                'data' => $teams 
            ], Response::HTTP_OK);
        } catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
