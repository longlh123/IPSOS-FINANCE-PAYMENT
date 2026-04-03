<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountDeposit;

class AccountDepositController extends Controller
{
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'account_type' => 'required|string|in:gotit,gotti-trade',
            'amount' => 'required|string|min:0'
        ]);

        $deposit = AccountDeposit::create([
            'account_type' => $request->account_type,
            'amount' => $request->amount
        ]);

        $totalDeposit = AccountDeposit::where('account_type', $request->account_type)
                                        ->sum('amount');

        return response()->json([
            'deposited' => $totalDeposit
        ]);
    }

    public function getGotItAccount(Request $request)
    {
        $totalDeposit = AccountDeposit::where('account_type', $request->account_type);
    }
}
