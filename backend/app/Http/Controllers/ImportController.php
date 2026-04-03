<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RecipientsImport;
use App\Models\TradeUnionRecipientList;
use Illuminate\Support\Facades\Auth;

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
}
