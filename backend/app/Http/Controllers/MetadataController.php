<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ProjectType;
use App\Models\Department;
use App\Models\Team;
use App\Models\Project;
use App\Models\Role;
use App\Http\Resources\ProjectResource;

class MetadataController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            $projectTypes = Cache::remember('project_types', 3600, fn() => ProjectType::all(['id', 'name']));
            $departments = Cache::remember('deparments', 3600, fn() => Department::all(['id', 'name']));
            $roles = Cache::remember('roles', 3600, fn() => Role::all(['id', 'name', 'department_id']));
            $teams = Cache::remember('teams', 3600, fn() => Team::whereIn('department_id', [2, 3])->get(['id', 'name']));

            return response()->json([
                'status_code' => 200,
                'message' => 'Metadata fetched successfully',
                'data' => [
                    'project_types' => $projectTypes,
                    'departments' => $departments,
                    'roles' => $roles,
                    'teams' => $teams
                ]
            ]);
        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 500,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
