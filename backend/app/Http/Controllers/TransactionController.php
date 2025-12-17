<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    public function show(Request $request, $projectId)
    {
        try{
            $perPage = $request->query('per_page', 10);
            $search = $request->query('searchTerm');
            $searchMonth = $request->query('searchMonth');
            $searchYear = $request->query('searchYear');

            $query = DB::table('project_respondents as pr')
                    ->leftJoin('project_vinnet_transactions as pvt', 'pr.id', '=', 'pvt.project_respondent_id')
                    ->leftJoin('project_gotit_voucher_transactions as pgt', 'pr.id', '=', 'pgt.project_respondent_id')
                    ->join('projects as p', 'p.id', '=', 'pr.project_id')
                    ->join('provinces', 'provinces.id', '=', 'pr.province_id')
                    ->join('employees', 'employees.id', '=', 'pr.employee_id')
                    ->select(
                        DB::raw('MONTH(COALESCE(pvt.created_at, pgt.created_at)) as `month`'),
                        DB::raw('COALESCE(pvt.vinnet_payservice_requuid, pgt.transaction_ref_id) AS transaction_id'),
                        'pr.service_code',
                        DB::raw('COALESCE(pvt.created_at, pgt.created_at) AS created_at'),
                        'pr.channel',
                        'pr.phone_number',
                        DB::raw('COALESCE(pvt.total_amt, pgt.voucher_value) AS amount'),
                        'pvt.discount',
                        'pvt.payment_amt',
                        DB::raw('COALESCE(pvt.payment_amt / 1.1, pgt.voucher_value) AS payment_pre_tax'),
                        DB::raw('COALESCE(pvt.vinnet_token_message, pgt.voucher_status) AS transaction_status'),
                        'p.project_name',
                        'provinces.name as province_name',
                        'p.internal_code',
                        DB::raw('(SELECT pd.symphony FROM project_details as pd WHERE pd.project_id = p.id) as symphony'),
                        'pr.shell_chainid',
                        'pr.respondent_id',
                        'pr.respondent_phone_number',
                        'employees.employee_id',
                        'employees.first_name',
                        'employees.last_name',
                        'pr.interview_start',
                        'pr.interview_end',
                        'pr.reject_message',
                        'pr.status as project_respondent_status'
                    )
                    ->where(function($q) {
                        $q->whereRaw("COALESCE(pvt.vinnet_token_message, pgt.voucher_status) IN ('Thành công', 'Voucher được cập nhật thành công.')")
                        ->orWhereRaw("COALESCE(pvt.vinnet_token_message, pgt.voucher_status) LIKE 'Voucher được cancelled by GotIt ngày%'");
                    })
                    ->whereIn('pr.channel', ['vinnet','gotit','other']);
            
            if($projectId != 0){
                $query->where('pr.project_id', $projectId);
            }

            if($search){
                Log::info('search: ' . $search);

                $query->where(function($q) use ($search){
                    $q->where('p.project_name', 'LIKE', "%$search%")
                        ->orWhere('p.internal_code', 'LIKE', "%$search%");  
                });
            }
            
            $query->when($searchMonth && $searchYear, function ($q) use ($searchMonth, $searchYear) {
                return $q->whereMonth(DB::raw('COALESCE(pvt.created_at, pgt.created_at)'), $searchMonth)
                        ->whereYear(DB::raw('COALESCE(pvt.created_at, pgt.created_at)'), $searchYear);
            });

            $query->when(!$searchMonth && $searchYear, function ($q) use ($searchYear) {
                return $q->whereYear(DB::raw('COALESCE(pvt.created_at, pgt.created_at)'), $searchYear);
            });
            
            if($request->query('export_all')){
                $transactions = $query->get();

                return response()->json([
                    'status_code' => 200,
                    'data' => TransactionResource::collection($transactions),
                    'meta' => []
                ]);
            } else {
                $transactions = $query->paginate($perPage);

                return response()->json([
                    'status_code' => 200,
                    'data' => TransactionResource::collection($transactions),
                    'meta' => [
                        'current_page' => $transactions->currentPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                        'last_page' => $transactions->lastPage(),
                    ]
                ]);
            }

            
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => 500,
                'message' => 'List of employees requested failed - ' . $e->getMessage(),
            ]);
        }
    }
}
