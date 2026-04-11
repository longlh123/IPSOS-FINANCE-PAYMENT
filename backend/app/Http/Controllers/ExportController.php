<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Facades\Auth;
use App\Services\VinnetService;

class ExportController extends Controller
{
    public function exportTransaction(Request $request, VinnetService $vinnetService)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date'
        ]);

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $logged_in_user = Auth::user()->username;

        ini_set('memory_limit', '512M');
        
        return (new TransactionsExport($fromDate, $toDate, $logged_in_user, $vinnetService))->download('gotit_transactions.xlsx');
    }

    
}
