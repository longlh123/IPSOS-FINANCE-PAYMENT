<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TechcombankSurveys;

class TechcombankSurveysController extends Controller
{
    public function index(Request $request){
        try
        {
            return response()->json([
                'status_code' => 400,
                'message' => 'Successfully',
                'data' => TechcombankSurveys::all()
            ]);
        } catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
