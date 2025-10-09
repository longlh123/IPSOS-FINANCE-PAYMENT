<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\RespondentResource;
use App\Models\Project;
use App\Models\ProjectRespondent;
use App\Models\ProjectIUURespondent;

class RespondentController extends Controller
{
    public function show($project_id)
    {
        try
        {
            $project = Project::with(['projectDetails','projectTypes'])->findOrFail($project_id);

            // $respondents = ProjectRespondent::with('project', 'respondent')
            //     ->where('project_id', $project_id)
            //     ->get();

            $targetTypes = ['IDI', 'HIV', 'FGD'];

            if($project->projectTypes->whereIn('name', $targetTypes)->isNotEmpty()){
                //Dự án thuộc team IUU
                // $respondents = ProjectIUURespondent::with('project');

                Log::info("Dự án thuộc team IUU");
                Log::info($project->projectTypes->pluck('name')->toArray());
                
                return response()->json([
                    'status_code' => Response::HTTP_OK, //200
                    'message' => 'Successful request.',
                    'data' => $project_id
                ]);
            } else {


            }
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage()
            ]);
        }
    }

    
}
