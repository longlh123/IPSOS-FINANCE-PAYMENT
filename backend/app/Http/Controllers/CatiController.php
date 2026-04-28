<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CATIRespondent;
use App\Models\CATIBatch;
use App\Models\Project;
use App\Http\Resources\CATIBatchResource;

class CatiController extends Controller
{
    public function index(Request $request, $projectId)
    {
        try
        {
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'searchTerm' => 'nullable|string|max:255'
            ]);

            $perPage = $validated['per_page'] ?? 10;
            $searchTerm = $validated['searchTerm'] ?? null;
            
            $query = CATIBatch::where('project_id', $projectId);

            if($searchTerm){
                $query->where(function($q) use ($searchTerm){
                    $q->where('name', 'LIKE', "%{$searchTerm}%");
                });
            }            

            $catiBatches = $query->paginate($perPage);

            return response()->json([
                'status_code' => 200,
                'message' => 'Successfully',
                'data' => CATIBatchResource::collection($catiBatches),
                'meta' => [
                    'current_page' => $catiBatches->currentPage(),
                    'per_page' => $catiBatches->perPage(),
                    'total' => $catiBatches->total(),
                    'last_page' => $catiBatches->lastPage(),
                ]
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 400,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroyBatch(Request $request, $projectId, $batchId)
    {
        try
        {
            $logged_in_user = Auth::user()->id;

            $project = Project::findOrFail($projectId);

            $batch = $project->catiBatches()->where('id', $batchId)->first();

            if($logged_in_user !== $batch->created_user_id){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'You are not allowed to delete this batch.'
                ], 403);
            }

            $toUsed = $batch->respondents()
                                ->where('status', '!=', 'New')
                                ->exists();

            if($toUsed){
                return response()->json([
                    'status_code' => 403,
                    'message' => "This batch can't delete."
                ], 403);
            }
            
            $batch->delete();

            return response()->json([
                'status_code' => 200,
                'message' => 'Deleted successfully.'
            ]);
        } catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 400,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function filters()
    {
        $data = Cache::remember('filters.all', 3600, function(){
            return DB::table('cati_respondents')
                    ->select('filter_1','filter_2','filter_3','filter_4')
                    ->get();
        });  

        return response()->json([
            'filter_1' => $data->pluck('filter_1')->unique()->values(),
            'filter_2' => $data->pluck('filter_2')->unique()->values(),
            'filter_3' => $data->pluck('filter_3')->unique()->values(),
            'filter_4' => $data->pluck('filter_4')->unique()->values(),
        ]);
    }

    public function next(Request $request)
    {
        $user = $request->user ?? 'SG999999';

        DB::beginTransaction();

        $query = DB::table('cati_respondents')
            ->where('status', 'New');

        if($request->filter_1){
            $query->where('filter_1', $request->filter_1);
        }
        
        if($request->filter_2){
            $query->where('filter_2', $request->filter_2);
        }

        if($request->filter_3){
            $query->where('filter_3', $request->filter_3);
        }

        if($request->filter_4){
            $query->where('filter_4', $request->filter_4);
        }

        $row = $query->lockForUpdate()->first();

        if (!$row) {
            DB::commit();
            return response()->json(null);
        }

        DB::table('cati_respondents')
            ->where('id', $row->id)
            ->update([
                'status' => 'Calling',
                'assigned_to' => $user,
                'locked_at' => now(),
            ]);

        DB::commit();

        return response()->json($row);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string',
            'comment' => 'nullable|string'
        ]);
        
        DB::table('cati_respondents')
            ->where('id', $request->id)
            ->update([
                'status' => $request->status,
                'comment' => $request->comment,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
    
    public function getSuspended(Request $request)
    {
        try
        {   
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'employee_id' => 'required|integer|exists:users,id',
                'searchTerm' => 'nullable|string|max:255'
            ]);

            $perPage = $validated['per_page'] ?? 10;
            $searchTerm = $validated['searchTerm'] ?? null;
            $employeeId = $validated['employee_id'] ?? null;

            $query = CATIRespondent::where('status', 'Suspended')
                                    ->where('assigned_to', $employeeId)
                                    ->orderBy('updated_at', 'desc');

            if($searchTerm){
                $query->where(function($q) use ($searchTerm){
                    $q->where('phone', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('respondent_id', 'LIKE', "%{$searchTerm}%");
                });
            }            

            $catiRespondents = $query->paginate($perPage);

            return response()->json([
                'status_code' => 200,
                'message' => 'Successfully',
                'data' => $catiRespondents,
                'meta' => [
                    'current_page' => $catiRespondents->currentPage(),
                    'per_page' => $catiRespondents->perPage(),
                    'total' => $catiRespondents->total(),
                    'last_page' => $catiRespondents->lastPage(),
                ]
            ]);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 400,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
