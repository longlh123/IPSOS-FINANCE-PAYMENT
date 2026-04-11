<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccountDeposit;
use App\Models\ProjectRespondent;
use App\Models\ProjectGotItVoucherTransaction;

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
        
        $spent = ProjectGotItVoucherTransaction::where('voucher_status', ProjectGotItVoucherTransaction::STATUS_VOUCHER_SUCCESS)
                            ->sum('amount');               

        return response()->json([
            'deposited' => $totalDeposit,
            'spent' => $spent,
            'balance' => $totalDeposit - $spent
        ]);
    }

    public function getGotItAccount(Request $request, $accountType)
    {
        $totalDeposit = AccountDeposit::where('account_type', $accountType)
                            ->sum('amount');

        $spent = ProjectGotItVoucherTransaction::where('voucher_status', ProjectGotItVoucherTransaction::STATUS_VOUCHER_SUCCESS)
                            ->sum('amount');

        return response()->json([
            'deposited' => $totalDeposit,
            'spent' => $spent,
            'balance' => $totalDeposit - $spent
        ]);
    }
}
