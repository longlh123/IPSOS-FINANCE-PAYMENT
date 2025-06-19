<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\ProjectVinnetToken;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends Controller
{
    public function show($project_id)
    {
        try
        {
            $employeeCounts = DB::table('project_vinnet_tokens as pvt')
            ->join('employees as e', 'e.id', '=', 'pvt.employee_id')
            ->join('projects as p', 'p.id', '=', 'pvt.project_id')
            ->select(
                'e.id', 
                'e.employee_id', 
                'e.first_name', 
                'e.last_name',
                DB::raw('(SELECT COUNT(*) FROM project_vinnet_tokens AS pvt_2 WHERE pvt.employee_id = pvt_2.employee_id AND pvt_2.project_id = ' . $project_id . ') AS number_of_transaction'),
                DB::raw('(SELECT COUNT(*) FROM project_vinnet_tokens AS pvt_2 WHERE pvt.employee_id = pvt_2.employee_id AND pvt_2.project_id = ' . $project_id . ' AND status LIKE "Token verified") AS number_of_transaction_succesfully'),
                DB::raw('(SELECT COUNT(*) FROM project_vinnet_tokens AS pvt_2 WHERE pvt.employee_id = pvt_2.employee_id AND pvt_2.project_id = ' . $project_id . ' AND status LIKE "Token verified" AND pvt_2.phone_number <> pvt_2.respondent_phone_number) AS number_of_phone'),
                DB::raw('(SELECT GROUP_CONCAT(pvt_2.reject_message ORDER BY pvt_2.reject_message SEPARATOR ", ") FROM project_vinnet_tokens AS pvt_2 WHERE pvt.employee_id = pvt_2.employee_id AND pvt_2.project_id = ' . $project_id . ' AND pvt_2.vinnet_token_status LIKE "Từ chối nhập số điện thoại để nhận quà.") AS reject_message')
            )
            ->where('pvt.project_id', '=', $project_id)
            ->groupBy('e.id', 'e.employee_id', 'e.first_name', 'e.last_name')
            ->get();


            return response()->json([
                'status_code' => Response::HTTP_OK,
                'message' => 'List of employees requested successfully',
                'data' => $employeeCounts
            ], Response::HTTP_OK);
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