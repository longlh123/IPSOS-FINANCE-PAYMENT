<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\RespondentResource;
use App\Models\ProjectRespondent;

class RespondentController extends Controller
{
    public function show($project_id)
    {
        try
        {
            $respondents = ProjectRespondent::with('project', 'respondent')
                ->where('project_id', $project_id)
                ->get();
            
            Log::info($project_id);

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => RespondentResource::collection($respondents)
            ]);
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
