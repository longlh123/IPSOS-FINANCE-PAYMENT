<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\ApiResponse;
use App\Models\Quotation;
use App\Http\Resources\OperationsResource;

class OperationsController extends Controller
{
    use ApiResponse;

    public function getOperations($projectId, $quotationId) : JsonResponse
    {
        $quotation = Quotation::findOrFail($quotationId);

        $operation = $quotation->operations;

        if (!$operation) {
            return $this->success(null, 'No operations found.');
        }

        return $this->success(new OperationsResource($operation), 'Successfully loaded operations.');
    }

    public function store(Request $request, $projectId, $quotationId)
    {
        try
        {
            $request->validate([
                'data' => 'required|array',
                'data.daily_interview_target' => 'required|int',
                'data.target_for_interviewers' => 'required|int',
                'data.interviewers_per_supervisor' => 'required|int'
            ]);

            $logged_in_user = Auth::user()->id;

            $quotation = Quotation::findOrFail($quotationId);

            $operations = $quotation->operations;

            if($operations){
                $operations->update([
                    'data' => $request->data
                ]);

                return $this->success(new OperationsResource($operations), 'Changes saved successfully.');
            } else {
                $operations = $quotation->operations()->create([
                    'data' => $request->data,
                    'status' => 'draft',
                    'created_user_id' => $logged_in_user
                ]);

                return $this->success(new OperationsResource($operations), 'Settings saved successfully.');
            }
        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return $this->error($e->getMessage(), $e->getMessage());
        }
    }
}
