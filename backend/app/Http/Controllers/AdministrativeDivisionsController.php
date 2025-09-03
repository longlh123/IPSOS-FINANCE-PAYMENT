<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Province;
use App\Models\GSO2025Province;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\OldProvinceResource;
use App\Http\Resources\OldDistrictResource;

class AdministrativeDivisionsController extends Controller
{
    public function get_old_provinces(Request $request){
        try{
            $provinces = Province::with(['region'])->get();

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of provinces requested successfully',
                'data' => OldProvinceResource::collection($provinces)
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_old_districts(Request $request, $provinceId)
    {
        try{
            $province = Province::find($provinceId);

            if($province){
                $districts = Province::with(['districts', 'districts.wards'])->findOrFail($provinceId);

                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'List of provinces requested successfully',
                    'data' => new OldDistrictResource($districts)
                ]);
            } else {
                $districts = Province::with(['region', 'districts'])->get();

                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'List of provinces requested successfully',
                    'data' => OldDistrictResource::collection($districts)
                ]);
            }
            
        } catch(\Exception $e){
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage()
            ]);
        }
    }

    public function get_provinces(Request $request){
        try{
            $provinces = GSO2025Province::with(['provinces', 'provinces.region'])->get();

            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of provinces requested successfully',
                'data' => ProvinceResource::collection($provinces)
            ], Response::HTTP_OK);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function get_districts(Request $request, $provinceId){
        try{
            $province = GSO2025Province::find($provinceId);

            if($province){
                $districts = GSO2025Province::with(['districts'])->findOrFail($provinceId);

                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'List of districts requested successfully',
                    'data' => new DistrictResource($districts)
                ]);
            } else {
                //Project ID không đúng => return all districts based on provinces
                $districts = GSO2025Province::with(['districts'])->get();

                return response()->json([
                    'status_code' => Response::HTTP_OK,
                    'message' => 'List of districts requested successfully',
                    'data' => DistrictResource::collection($districts)
                ]);
            }
        } catch(\Exception $e){
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ]);
        }
    }
}
