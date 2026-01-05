<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProjectRespondentTokenRequest;
use App\Models\InterviewURL;
use App\Models\Project;
use App\Models\ProjectRespondent;
use Ramsey\Uuid\Uuid;
use App\Constants\TransactionStatus;
use App\Services\ProjectRespondentTokenService;
use App\Services\VinnetService;

class TransactionController extends Controller
{
    public function verify(Request $request, ProjectRespondentTokenService $tokenService)
    {
        try{
            $recordToken = $tokenService->verifyToken($request->token);

            return response()->json(['valid' => true]);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function authenticate_token(ProjectRespondentTokenRequest $request, ProjectRespondentTokenService $tokenService, VinnetService $vinnetService){
        try
        {
            $validatedRequest = $request->validated();

            $decodedURL = base64_decode($validatedRequest['url']);

            if (!$decodedURL || !str_contains($decodedURL, '/')) {
                return response()->json([
                    'status_code' => 901,
                    'message' => 'URL không hợp lệ.',
                    'error' => 'INVALID_URL'
                ], 400);
            }

            $splittedURL = explode("/", $decodedURL);

            Log::info('URL Splitted: ' . json_encode($splittedURL));
            
            try{

                $interviewURL = new InterviewURL($splittedURL);

            } catch (\Exception $e){
                return response()->json([
                    'status_code' => 901,
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage()
                ], 404);
            }

            //Tìm thông tin dự án dựa trên dữ liệu từ Interview URL
            $project = Project::findByInterviewURL($interviewURL);

            if (!$project->projectDetails) {
                return response()->json([
                    'status_code' => 902,
                    'message' => Project::ERROR_PROJECT_DETAILS_NOT_CONFIGURED . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::ERROR_PROJECT_DETAILS_NOT_CONFIGURED
                ], 404);
            }

            //Tìm thông tin dự án đã được set up giá dựa trên dữ liệu từ Interview URL
            try {
                $price = $project->getPriceForProvince($interviewURL->province_id, $interviewURL->price_level);
            } catch(\Exception $e){

                Log::error($e->getMessage());

                return response()->json([
                    'status_code' => 903,
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 404);
            }

            Log::info('Find the price by province: ' . intval($price));

            if($price == 0)
            {   
                Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                
                return response()->json([
                    'status_code' => 903,
                    'message' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 422);
            }
            
            $gifts = ['gotit'];

            $phoneNumber = $validatedRequest['phone_number'];

            $serviceCode = $vinnetService->get_service_code($phoneNumber);

            if(!$serviceCode){
                return response()->json([
                    'status_code' => 904,
                    'message' => ProjectRespondent::ERROR_INVALID_RESPONDENT_PHONE_NUMBER . 'Vui lòng kiểm tra lại.',
                    'error' => ProjectRespondent::ERROR_INVALID_RESPONDENT_PHONE_NUMBER
                ], 409);
            }

            try{
                $tokenData = $vinnetService->authenticate_token();

                if($tokenData['code'] == 0){
                    $serviceItemsData = $vinnetService->query_service(
                                            $phoneNumber, 
                                            $serviceCode, 
                                            $tokenData['token'], 
                                            null
                                        );

                    if($serviceItemsData['code'] == 0){
                        foreach($serviceItemsData['service_items'] as $serviceItem){
                            
                            if($serviceItem['itemValue'] === $price){
                                $gifts[] = 'vinnet';
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $gifts[] = 'gotit';
            }

            Log::info('Gift items by price: ' . implode(",", $gifts));

            try
            {
                //Kiểm tra đáp viên đã thực hiện giao dịch nhận quà trước đó hay chưa?
                ProjectRespondent::checkIfRespondentProcessed($project, $interviewURL);

                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $phoneNumber);

            } catch(\Exception $e){
                return response()->json([
                    'status_code' => 905,
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => $e->getMessage()
                ], 409);
            }

            // Tìm thông tin của Project Respondent
            $projectRespondent = ProjectRespondent::findProjectRespondent($project, $interviewURL);

            if(!$projectRespondent){
                //Thông tin mới => Cập nhật thông tin vào hệ thống
                DB::beginTransaction();

                try{
                    $projectRespondent = $project->createProjectRespondents([
                        'project_id' => $project->id,
                        'shell_chainid' => $interviewURL->shell_chainid,
                        'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id,
                        'employee_id' => $interviewURL->employee->id,
                        'province_id' => $interviewURL->province_id,
                        'interview_start' => $interviewURL->interview_start,
                        'interview_end' => $interviewURL->interview_end,
                        'respondent_phone_number' => $interviewURL->respondent_phone_number,
                        'phone_number' => $phoneNumber,
                        'service_code' => $serviceCode,
                        'price_level' => $interviewURL->price_level,
                        'status' => ProjectRespondent::STATUS_RESPONDENT_PENDING
                    ]);
                    
                    DB::commit();

                } catch(\Exception $e) {

                    DB::rollBack();

                    Log::error('SQL Error ['.$e->getCode().']: '.$e->getMessage());

                    if($e->getCode() === '23000'){
                        return response()->json([
                            'status_code' => 989,
                            'message' => ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT,
                            'error' => $e->getMessage()
                        ], 409); // 409 Conflict
                    }
                    
                    return response()->json([
                        'status_code' => 989,
                        'message' => ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT,
                        'error' => $e->getMessage()
                    ], 500);
                }
            } else {
                //Thông tin cũ
                //Kiểm tra xem Project Respondent có thực hiện bất kỳ giao dịch nào chưa?
                //Nếu chưa => xem như thông tin mới => cập nhật lại status cho Project Respondent

                Log::info("Number of transactions: " . $projectRespondent->vinnetTransactions()->count());

                if($projectRespondent->vinnetTransactions()->count() == 0){
                    
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_PENDING);
                } else {
                    Log::info(ProjectRespondent::ERROR_DUPLICATE_RESPONDENT . ' [Trường hợp Đáp viên đã tồn tại và đã có thực hiện giao dịch]');

                    //Nếu đã thực hiện giao dịch => không cho thực hiện
                    return response()->json([
                        'status_code' => 905,
                        'message' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT,
                        'error' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT . ' [Trường hợp Đáp viên đã tồn tại và đã có thực hiện giao dịch]'
                    ], 500);
                }
            }

            $token = $tokenService->createOrReuseToken($projectRespondent);

            return response()->json([
                'status_code' => 900,
                'message' => ProjectRespondent::STATUS_RESPONDENT_QUALIFIED,
                'token' => $token,
                'gifts' => $gifts
            ], 200);

        } catch(\Exception $e){
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => 989,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

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
