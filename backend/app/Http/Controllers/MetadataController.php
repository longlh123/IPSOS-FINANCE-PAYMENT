<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectType;
use App\Models\Department;
use App\Models\Team;
use App\Models\Project;
use App\Http\Resources\ProjectResource;

class MetadataController extends Controller
{
    public function index(Request $request)
    {
        try{
            $projects = Project::with(['projectDetails','projectTypes'])->get();
            $projectTypes = ProjectType::all(['id', 'name']);
            $departments = Department::all(['id', 'name']);
            
            $teams = Team::where('department_id', 3)->get(['id', 'name']);

            // $lastestYear = ProjectDetail::selectRaw('YEAR()')

            return response()->json([
                'status_code' => 200,
                'message' => 'List of project types requested successfully',
                'data' => [
                    'projects' => ProjectResource::collection($projects),
                    'project_types' => $projectTypes,
                    'departments' => $departments,
                    'teams' => $teams
                ]
            ]);
        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 500,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
