<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Province;

class AdministrativeDivisionsController extends Controller
{
    public function index(Request $request){
        try{
            $provinces = Province::all();
            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of provinces requested successfully',
                'data' => $provinces
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
