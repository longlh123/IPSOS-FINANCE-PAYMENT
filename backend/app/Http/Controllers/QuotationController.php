<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;
use App\Models\Quotation;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\QuotationVersionResource;

class QuotationController extends Controller
{
    public function getQuotationVersions($projectId)
    {
        try
        {
            $project = Project::findOrFail($projectId);

            $quotationVersions = $project->quotations()
                                            ->orderByDesc('version')
                                            ->get();

            return response()->json([
                'status_code' => 200,
                'project' => new ProjectResource($project),
                'versions' => QuotationVersionResource::collection($quotationVersions)
            ],200);
        } catch(\Exception $e)
        {
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getQuotation($projectId, $versionId)
    {
        try
        {
            Log::info("A");

            $quotation = Quotation::where('project_id', $projectId)
                            ->where('version', $versionId)
                            ->first();

            Log::info("A1");

            if(!$quotation){
                return response()->json([
                    'status_code' => 200,
                    'quotation' => null,
                    'message' => 'Successful.'
                ]);
            }

            return response()->json([
                'status_code' => 200,
                'quotation' => $quotation,
                'message' => 'Successful.'
            ]);
        } catch(\Exception $e)
        {
            Log::error($e->getMessage());

            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request, $projectId)
    {
        try
        {
            $logged_in_user = Auth::user()->id;

            try
            {
                $project = Project::findOrFail($projectId);
            }
            catch(\Exception $e)
            {
                Log::error('The project not found: ' . $e->getMessage());
                return response()->json([
                    'status_code' => Response::HTTP_NOT_FOUND, //404
                    'message' => 'The project not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $lastestVersion = $project->quotations()->max('version') ?? 0;

            $quotation = $project->quotations()->create([
                'data' => $request->data,
                'version' => $lastestVersion + 1,
                'status' => 'draft',
                'created_user_id' => $logged_in_user
            ]);
            
            return response()->json([
                'status_code' => 200,
                'quotation' => $quotation,
                'message' => 'The quotation stored successfully.'
            ]);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(Request $request, $projectId, $versionId)
    {
        try
        {
            $logged_in_user = Auth::user()->id;

            $project = Project::findOrFail($projectId);

            $quotation = $project->quotations()->where('id', $versionId)->first();

            if($quotation->status !== 'draft' && $quotation->status != 'rejected'){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Cannot edit this quotation.'
                ], 403);
            }

            $quotation->update([
                'data' => $request->data,
                'updated_user_id' => $logged_in_user
            ]);

            return response()->json([
                'status_code' => 200,
                'quotation' => new QuotationVersionResource($quotation),
                'message' => 'The quotation updated successfully.'
            ]);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy($projectId, $versionId)
    {
        try
        {
            $logged_in_user = Auth::user()->id;

            $project = Project::findOrFail($projectId);

            $quotation = $project->quotations()->where('id', $versionId)->first();

            if($quotation->status !== 'draft'){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Only draft version can be deleted.'
                ], 403);
            }

            if($logged_in_user !== $quotation->created_user_id){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'You are not allowed to delete this draft.'
                ], 403);
            }

            $quotation->delete();

            return response()->json([
                'status_code' => 200,
                'quotation' => new QuotationVersionResource($quotation),
                'message' => `Draft version ${$quotation->version} deleted successfully.`
            ]);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function submit($quotationId)
    {
        try
        {
            $quotation = Quotation::findOrFail($quotationId);

            if($quotation->status !== 'draft'){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Only draft can be submitted.'
                ], 403);
            }

            $quotation->update([
                'status' => 'submitted'
            ]);

            return response()->json([
                'status_code' => 200,
                'quotation' => $quotation,
                'message' => 'The quotation submitted successfully.'
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function approve($quotationId)
    {
        try
        {
            $logged_in_user = Auth::user()->id;

            $quotation = Quotation::findOrFail($quotationId);

            if($quotation->status !== 'submit'){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Only submitted can be approved.'
                ], 403);
            }

            $quotation->update([
                'status' => 'approved',
                'approved_user_id' => $logged_in_user,
                'approved_at' => now()
            ]);

            return response()->json([
                'status_code' => 200,
                'quotation' => $quotation,
                'message' => 'The quotation approved successfully.'
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reject($quotationId)
    {
        try
        {
            $quotation = Quotation::findOrFail($quotationId);

            if($quotation->status !== 'submitted'){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Only submitted can be rejected.'
                ], 403);
            }

            $quotation->update([
                'status' => 'rejected'
            ]);

            return response()->json([
                'status_code' => 200,
                'quotation' => $quotation,
                'message' => 'The quotation rejected successfully.'
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
