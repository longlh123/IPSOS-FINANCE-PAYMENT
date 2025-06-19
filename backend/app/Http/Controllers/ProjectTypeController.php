<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ProjectType;

class ProjectTypeController extends Controller
{
    public function index(Request $request){
        try{
            $project_types = ProjectType::pluck('name'); // Fetch only the specified column

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of project types requested successfully',
                'data' => $project_types
            ], Response::HTTP_OK);
        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        
    }
}
