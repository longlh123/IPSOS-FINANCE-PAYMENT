<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\CATIBatch;
use App\Imports\CATIRespondentsImport;

class ImportController extends Controller
{
    public function importRecipients(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'list_name' => 'required|string'
        ]);

        $logged_in_user = Auth::user()->id;

        $list = TradeUnionRecipientList::firstOrCreate([
            'name' => $request->list_name,
            'created_by' => $logged_in_user
        ]);
        
        try {
            Excel::import(new RecipientsImport($list->id), $request->file('file'));
            
            return response()->json([
                'status_code' => 200,
                'message' => 'Recipients imported successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'error' => 'Failed to import recipients: ' . $e->getMessage()
            ]);
        }
    }

    public function importOfflineProjectRespondents(Request $request, $projectId)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', //10MB
        ]);

        $logged_in_user = Auth::user()->id;

        $batchId = "IMPORT_" . now()->format('dmYHis');

        try 
        {
            $import = new OfflineRespondentImport($batchId);

            Excel::import($import, $request->file('file'));

            
        }catch (\Throwable $e) {
            \Log::error('Project Respondents import failed', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
            ]);

            return response()->json([
                'message' => 'Failed to import offline respondents'
            ], 500);
        }
    }

    public function importCATIRespondents(Request $request, $projectId)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', //10MB
            'batch_name' => 'required|string'
        ]);

        $batchName = trim($validated['batch_name']) ?? null;

        $logged_in_user = Auth::user()->id;

        $batch = CATIBatch::firstOrCreate([
            'project_id' => $projectId,
            'name' => $batchName,
            'status' => 'active',
            'created_user_id' => $logged_in_user
        ]);

        try 
        {
            $import = new CATIRespondentsImport($batch->project_id, $batch->id);

            Excel::import($import, $request->file('file'));

            // \Log::info('Failures: ', $import->failures()->toArray());
            // \Log::info('Errors: ', $import->errors()->toArray());

            $totalRecords = $batch->respondents()->count();

            $batch->update([
                'total_records' => $totalRecords
            ]);

            if ($import->failures()->isNotEmpty() || $import->errors()->isNotEmpty()) {
                return response()->json([
                    'message' => 'Import completed with validation errors',
                    'failures' => $import->failures(),
                    'errors' => $import->errors()
                ], 422);
            }

            return response()->json([
                'message' => 'Respondents imported successfully'
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('CATI import failed', [
                'error' => $e->getMessage(),
                'project_id' => $projectId,
            ]);

            return response()->json([
                'message' => 'Failed to import CATI respondents'
            ], 500);
        }
    }
}
