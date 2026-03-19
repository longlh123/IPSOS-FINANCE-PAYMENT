<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CatiController extends Controller
{
    public function filters()
    {
        return response()->json([
            'filter_1' => DB::table('cati_respondents')->distinct()->groupBy('filter_1')->pluck('filter_1'),
            'filter_2' => DB::table('cati_respondents')->distinct()->groupBy('filter_2')->pluck('filter_2'),
            'filter_3' => DB::table('cati_respondents')->distinct()->groupBy('filter_3')->pluck('filter_3'),
            'filter_4' => DB::table('cati_respondents')->distinct()->groupBy('filter_4')->pluck('filter_4'),
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
        $employeeId = $request->employee_id;

        $data = DB::table('cati_respondents')
            ->where('status', 'Suspended')
            ->where('assigned_to', $employeeId)
            ->orderBy('updated_at', 'desc')
            ->get([
                'id',
                'respondent_id',
                'phone',
                'link',
                'comment'
            ]);

        return response()->json($data);
    }
}
