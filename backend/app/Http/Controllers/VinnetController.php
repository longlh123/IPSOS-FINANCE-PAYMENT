<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;
use App\Models\Employee;
use App\Models\ProjectVinnetTransaction;
use App\Models\ProjectVinnetSMSTransaction;
use App\Models\ProjectRespondent;
use App\Models\InterviewURL;
use App\Models\VinnetUUID;
use App\Models\ENVObject;
use App\Models\APICMCObject;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\VinnetProjectResource;
use App\Constants\SMSStatus;
use App\Constants\TransactionStatus;
use App\Services\ProjectRespondentTokenService;
use App\Services\VinnetService;

class VinnetController extends Controller
{
    /** 
     * Get the merchant information
     * 
     * @param string $request
     * @return json
     * @throws Exception
    */
    public function get_merchant_info(Request $request)
    {
        try{
            $envObject = new ENVObject();
            
            Log::info('Enviroment: ' . $envObject->environment);

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => $envObject->merchantInfo
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function findSubsets($nums, $target_sum) 
    {
        $subset = [];
        rsort($nums); // Sort in reverse order
    
        function backtrack($index, $current_sum, $nums, $target_sum, &$subset) {
            if ($current_sum == $target_sum) {
                return;
            }
    
            if ($index == count($nums) || $current_sum > $target_sum) {
                return;
            }
    
            $subset[] = $nums[$index];
            backtrack($index, $current_sum + $nums[$index], $nums, $target_sum, $subset);
    
            if ($current_sum + $nums[$index] > $target_sum) {
                array_pop($subset);
                backtrack($index + 1, $current_sum, $nums, $target_sum, $subset);
            }
        }
    
        backtrack(0, 0, $nums, $target_sum, $subset);
        return $subset;
    }
    
    public function reject_transaction(Request $request)
    {
        try
        {
            $request->validate([
                'url' => 'required|string',
                'reject_message' => 'required|string|min:3'
            ], [
                'url.required' => 'Yêu cầu từ chối không hợp lệ.',
                'url.string' => 'Yêu cầu từ chối không hợp lệ.', 
                'reject_message.required' => 'Lý do từ chối là bắt buộc.',
                'reject_message.string' => 'Lý do từ chối phải là một đoạn văn bản hợp lệ.',
                'reject_message.min' => 'Lý do từ chối phải là một đoạn văn bản hợp lệ.'
            ]);

            $url = $request->input('url');
            $reject_message = $request->input('reject_message');

            $decodedURL = base64_decode($url);

            if (!$decodedURL || !str_contains($decodedURL, '/')) {
                return response()->json([
                    'message' => 'URL không hợp lệ.',
                    'error' => 'INVALID_URL'
                ], 400);
            }

            $splittedURL = explode("/", $decodedURL);

            Log::info('URL Splitted: ' . json_encode($splittedURL));
            
            try{

                $interviewURL = new InterviewURL($splittedURL);

                if(!in_array($interviewURL->channel, ['gotit','vinnet','other'])){
                    Log::error('URL Vinnet nhưng thông tin đường link là quà Got It.');
                    throw new \Exception(ProjectRespondent::ERROR_INVALID_INTERVIEWERURL);
                }

            } catch (\Exception $e){
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage()
                ], 404);
            }

            //Tìm thông tin dự án dựa trên dữ liệu từ Interview URL
            $project = Project::findByInterviewURL($interviewURL);

            if (!$project->projectDetails) {
                return response()->json([
                    'message' => Project::ERROR_PROJECT_DETAILS_NOT_CONFIGURED . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::ERROR_PROJECT_DETAILS_NOT_CONFIGURED
                ], 404);
            }

            if($project->projectDetails->status === Project::STATUS_IN_COMING || $project->projectDetails->status === Project::STATUS_ON_HOLD || 
                ($project->projectDetails->status === Project::STATUS_ON_GOING && !in_array(substr(strtolower($interviewURL->location_id), 0, 2), ['hn', 'sg', 'dn', 'ct']))){
                    
                    Log::info('Staging Environment: ');
                    
                    return response()->json([
                        'message' => TransactionStatus::STATUS_TRANSACTION_TEST . ' [Ghi nhận lý do từ chối của đáp viên]'
                    ], Response::HTTP_OK);
            } 

            Log::info('Live Environment:');

            try
            {
                //Kiểm tra đáp viên đã thực hiện giao dịch nhận quà trước đó hay chưa?
                ProjectRespondent::checkIfRespondentProcessed($project, $interviewURL);

            } catch(\Exception $e){
                return response()->json([
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
                        'phone_number' => $interviewURL->respondent_phone_number,
                        'price_level' => $interviewURL->price_level,
                        'channel' => $interviewURL->channel,
                        'service_code' => '',
                        'status' => ProjectRespondent::STATUS_RESPONDENT_PENDING
                    ]);

                    DB::commit();

                } catch(\Exception $e) {

                    DB::rollBack();

                    Log::error('SQL Error ['.$e->getCode().']: '.$e->getMessage());

                    if($e->getCode() === '23000'){
                        return response()->json([
                            'message' => ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT,
                            'error' => $e->getMessage()
                        ], 409); // 409 Conflict
                    }
                    
                    return response()->json([
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
                        'message' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT,
                        'error' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT . ' [Trường hợp Đáp viên đã tồn tại và đã có thực hiện giao dịch]'
                    ], 500);
                }
            }

            if (!$projectRespondent) {
                throw new \Exception(ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT);
            }

            $projectRespondent->update([
                'reject_message' => $reject_message,
                'status' => ProjectRespondent::STATUS_RESPONDENT_REJECTED
            ]);

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id, 'reject_message' => $reject_message]);
            
            return response()->json([
                'message' => 'Cảm ơn bạn đã chia sẻ lý do!'
            ], 200);
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * 
     * Perform multiple transactions simultaneously
     * 
     * @param request
     * 
     * @return string
     * 
     */
    public function perform_multiple_transactions(TransactionRequest $request, ProjectRespondentTokenService $tokenService, VinnetService $vinnetService)
    {
        $step_info = "";

        try
        {
            $validatedRequest = $request->validated();

            $token = $validatedRequest['token'] ?? null;
            $serviceType = $validatedRequest['service_type'] ?? null;
            $serviceCode = $validatedRequest['service_code'] ?? null;
            $phoneNumber = $validatedRequest['phone_number'] ?? null;

            Log::info('Transaction Info: ', [
                'token' => $token,
                'service_type' => $serviceType,
                'service_code' => $serviceCode,
                'phone_number' => $phoneNumber
            ]);

            if(in_array($serviceCode, ['S0029','S0013','S0031','S0015'])){
                Log::error(ProjectRespondent::ERROR_MOBILE_NETWORK_NOT_SUPPORTED);
                throw new \Exception(ProjectRespondent::ERROR_MOBILE_NETWORK_NOT_SUPPORTED . ' Vui lòng dùng số điện thoại khác nếu có hoặc PVV có thể gửi quà trực tiếp cho bạn.');
            }

            Log::info('Starting');

            $tokenRecord = $tokenService->verifyToken($token);

            $projectRespondent = $tokenRecord->projectRespondent;
            
            $project = $projectRespondent->project;
            
            Log::info('Project: ' . $project);

            try
            {
                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $phoneNumber);

            } catch(\Exception $e){
                return response()->json([
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => $e->getMessage()
                ], 409);
            }

            //Tìm thông tin dự án đã được set up giá dựa trên dữ liệu từ Interview URL
            Log::info('Find the price item by province');
            
            try {
                $price = $project->getPriceForProvince($projectRespondent->province_id, $projectRespondent->price_level);
            } catch(\Exception $e){

                Log::error($e->getMessage());

                return response()->json([
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 404);
            }

            Log::info('Price: ' . intval($price));
            
            if($project->projectDetails->status === Project::STATUS_IN_COMING || $project->projectDetails->status === Project::STATUS_ON_HOLD || 
                ($project->projectDetails->status === Project::STATUS_ON_GOING && $projectRespondent->employee_id === 1)){
                    
                    Log::info('Staging Environment: ');

                    $tokenRecord->update([
                        'status' => 'blocked'
                    ]);
                    
                    return response()->json([
                        'message' => TransactionStatus::STATUS_TRANSACTION_TEST . ' [Giá trị quà tặng: ' . $price . ']'
                    ], 200);
            } 

            Log::info('Live Environment:');
            
            if($price == 0)
            {   
                Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                
                return response()->json([
                    'message' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 422);
            }

            try
            {
                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $validatedRequest['phone_number']);

            } catch(\Exception $e){
                return response()->json([
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => $e->getMessage()
                ], 409);
            }

            $projectRespondent->update([
                'phone_number' => $phoneNumber,
                'service_code' => $serviceCode,
                'service_type' => $serviceType
            ]); 
            
            Log::info('Project respondent: ' . json_encode($projectRespondent->toArray()));
            
            Log::info('Authentication Token to make API calls and retrieve information about suitable carrier pricing tiers.');
            
            $step_info = "Authentication Token API";

            try{
                $tokenData = $vinnetService->authenticate_token();

                if($tokenData['code'] != 0)
                {
                    Log::error('Authentication Token API Exception: ' . $tokenData['message']);

                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_TEMPORARY_ERROR);
                    
                    return response()->json([
                        'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY,
                        'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY
                    ], 404);
                }
            } catch (\Throwable $e) {
                Log::error('Authentication Token API Exception: ' . $e->getMessage());

                if(isset($projectRespondent)){
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_TEMPORARY_ERROR);
                }

                return response()->json([
                    'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY,
                    'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY
                ], 404);
            }

            $step_info = "Query Service API";

            try{
                $serviceItemsData = $vinnetService->query_service(
                                            $validatedRequest['phone_number'], 
                                            $validatedRequest['service_code'], 
                                            $tokenData['token'], 
                                            null
                                        );
                                        
                if($serviceItemsData['code'] != 0)
                {
                    Log::error('Query Service API Exception: ' . $serviceItemsData['message']);

                    if(isset($projectRespondent)){
                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_TEMPORARY_ERROR);
                    }

                    return response()->json([
                        'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY,
                        'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY
                    ], 404);
                }
            } catch(\Throwable $e){
                Log::error('Query Service API Exception: ' . $e->getMessage());

                if(isset($projectRespondent)){
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_TEMPORARY_ERROR);
                }

                return response()->json([
                    'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY,
                    'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_TEMPORARY
                ], 404);
            }
            
            $prices = array();

            foreach($serviceItemsData['service_items'] as $serviceItem){
                
                if($serviceItem['itemValue'] !== 0){
                    array_push($prices, $serviceItem['itemValue']);
                }
            }

            $selectedPrices = array();

            if(in_array($price, $prices)){
                array_push($selectedPrices, $price);
            } else {
                $selectedPrices = $this->findSubsets($prices, $price);
            }

            if(empty($selectedPrices)){
                return response()->json([
                    'message' => TransactionStatus::STATUS_INVALID_DENOMINATION,
                    'error' => 'Không tìm được mệnh giá phù hợp.'
                ], 404);
            }
            
            Log::info('Selected Prices: ' . implode(", ", $selectedPrices));
            
            $vinnet_token_order = 1;

            $messagesToSend = [];
            $allSuccess = true;
            $errorMessages = [];
            $totalSuccess = 0;

            foreach($selectedPrices as $selectedPrice)
            {
                Log::info('Transaction ' . $vinnet_token_order . ': ' . $selectedPrice);

                $payServiceUuid = $vinnetService->generate_formated_uuid();
                Log::info('Pay Service UUID: ' . $payServiceUuid);

                $vinnetTransaction = $projectRespondent->createVinnetTransactions([
                    'project_respondent_id' => $projectRespondent->id,
                    'vinnet_serviceitems_requuid' => $serviceItemsData['reqUuid'],
                    'vinnet_payservice_requuid' => $payServiceUuid,
                    'vinnet_token_requuid' => $tokenData['reqUuid'],
                    'vinnet_token' => $tokenData['token'],
                    'vinnet_token_order' => $vinnet_token_order,
                    'vinnet_token_status' => TransactionStatus::STATUS_PENDING_VERIFICATION
                ]);

                $selectedServiceItem = null;

                foreach($serviceItemsData['service_items'] as $serviceItem){
                
                    if($serviceItem['itemValue'] === $selectedPrice){
                        $selectedServiceItem = $serviceItem;
                        break;
                    }
                }

                Log::info('Selected Service Item: '  . json_encode($selectedServiceItem));

                $step_info = "Pay Service API";

                try
                {
                    $payItemData = $vinnetService->pay_service(
                                            $payServiceUuid, 
                                            $validatedRequest['phone_number'], 
                                            $validatedRequest['service_code'], 
                                            $tokenData['token'], 
                                            $selectedServiceItem
                                        );
                } catch (\Throwable $e) {
                    Log::error("Pay Service API Exception [UUID: " . $payServiceUuid . "]: " . $e->getMessage());

                    if(isset($vinnetTransaction)){
                        $vinnetTransaction->update([
                            'vinnet_token_status' => TransactionStatus::STATUS_ERROR,
                            'vinnet_token_message' => $e->getMessage()
                        ]);
                    }

                    if(isset($projectRespondent)){
                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_API_FAILED);
                    }

                    return response()->json([
                        'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                        'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                    ], 400);
                }
                
                if($payItemData['code'] == 0)
                {
                    $statusPaymentServiceResult = $vinnetTransaction->updatePaymentServiceStatus($payItemData['reqUuid'], $payItemData['pay_item'], TransactionStatus::STATUS_VERIFIED, $payItemData['message']);
                    
                    if(!empty($payItemData['pay_item']['cardItems']) && is_array($payItemData['pay_item']['cardItems'])){
                        //TH Đáp viên chọn thẻ nạp tiền

                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_DISPATCHED);

                        $smsTransaction = $vinnetTransaction->createVinnetSMSTransaction([
                            'vinnet_transaction_id' => $vinnetTransaction->id,
                            'sms_status' => SMSStatus::PENDING
                        ]);
                        
                        $card_item = $payItemData['pay_item']['cardItems'][0];
                        
                        $messagesToSend[] = sprintf(
                            "%s:Code:%s,Seri:%s,Exp:%s",
                            number_format($payItemData['pay_item']['totalAmt'] / 1000, 0) . 'K',
                            $card_item['pinCode'] ?? 'N/A',
                            $card_item['serialNo'] ?? 'N/A',
                            $card_item['expiryDate'] ?? 'N/A'
                        );
                    } 

                    $totalSuccess = $totalSuccess + $payItemData['pay_item']['totalAmt'];
                }
                else 
                {
                    $allSuccess = false;
                    
                    $transactionErrorMessage = "";

                    if($payItemData['code'] == 99){
                        $transactionErrorMessage = TransactionStatus::ERROR_C99  . ' [' . $payItemData['message'] . ']';
                    } else {
                        $transactionErrorMessage = TransactionStatus::ERROR_C98  . ' [' . $payItemData['message'] . ']';
                    }

                    Log::error("Pay Service API Exception [UUID: " . $payServiceUuid . "]: " . $transactionErrorMessage);

                    if(isset($vinnetTransaction)){
                        $vinnetTransaction->update([
                            'vinnet_token_status' => TransactionStatus::STATUS_ERROR,
                            'vinnet_token_message' => $transactionErrorMessage
                        ]);
                    }

                    if(isset($projectRespondent)){
                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_PARTIAL);
                    }

                    return response()->json([
                        'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                        'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                    ], 400);
                }

                $vinnet_token_order++;
            }

            //TH Đáp viên chọn thẻ nạp tiền
            if($allSuccess){
                if(!empty($messagesToSend)) {
                    //TH Đáp viên chọn thẻ nạp tiền
                    $finalMessage =  implode("\n", $messagesToSend);

                    $messageCard = sprintf(
                        "IPSOS cam on ban. Tang ban ma dien thoai:\n%s",
                        $finalMessage ?? 'N/A'
                    );
                    
                    $apiCMCObject = new APICMCObject();
                    
                    try{
                        $responseSMSData = $apiCMCObject->send_sms($validatedRequest['phone_number'], $messageCard);
                    } catch(\Exception $e){
                        Log::error("CMC Telecom API Error: " . $e->getMessage());
                    
                        if(isset($smsTransaction)){
                            $smsTransaction->update([
                                'sms_status' => $e->getMessage()
                            ]);
                        }

                        if(isset($projectRespondent)){
                            $projectRespondent->updateStatus(ProjectRespondent::STATUS_API_FAILED);
                        }

                        return response()->json([
                            'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                            'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                        ], 404);
                    }
                    
                    if(intval($responseSMSData['status']) == 1){
                        $smsTransactionStatus = $smsTransaction->updateStatus(SMSStatus::SUCCESS, intval($responseSMSData['countSms']));

                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED);

                        return response()->json([
                            'message' => TransactionStatus::SUCCESS
                        ], 200);
                    } else {
                        $smsTransactionStatus = $smsTransaction->updateStatus($responseSMSData['statusDescription'], 0);

                        $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_NOT_RECEIVED);

                        return response()->json([
                            'message' => SMSStatus::ERROR,
                            'error' => SMSStatus::ERROR
                        ], 400);
                    }
                } else {
                    //TH Đáp viên chọn nạp tiền điện thoại trực tiếp
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED);

                    return response()->json([
                        'message' => TransactionStatus::SUCCESS
                    ], 200);
                }
            } else {
                $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_PARTIAL);

                Log::info('Tổng số tiền giao dịch thành công đáp viên nhận được: ' . $totalSuccess);
                Log::info('Thông báo lỗi giao dịch: ', $errorMessages);

                return response()->json([
                    'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                    'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                ], 400);
            }
        } catch(\Exception $e) {
            
            Log::error($e->getMessage());
            
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 
     * Change key: Thay đổi mã truy cập API (merchant key).
     * 
     * @param $request
     * 
     * @return string
     * 
     */
    public function change_key(Request $request, VinnetService $vinnetService)
    {
        try{
            Log::info('Changing key');

            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            $uuid = $vinnetService->generate_formated_uuid();
            Log::info('UUID: ' . $uuid);

            $reqData = $this->encrypt_data(json_encode(['oldMerchantKey' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_KEY'])]));
            
            $signature = $this->generate_signature(str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            Log::info('Data post: ' . json_encode($postData));
            
            $response = $this->post_vinnet_request(str_replace('"', '', $url) . '/changekey', null, $postData);

            $decodedResponse = json_decode($response, true);

            if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }
            
            if (!is_array($decodedResponse)) {
                throw new \Exception('Decoded services data is not an array');
            }

            Log::info('Decoded Response Data: ' . json_encode($decodedResponse));

            Log::info('Decoded resData: '. $decodedResponse['resData']);

            $decryptedData = $this->decrypt_data($decodedResponse['resData']);

            $decodedData = json_decode($decryptedData, true);
            
            Log::info('Decoded New Merchant Key: ' . $decodedData);

            if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }
            
            $decodedData = json_decode($decodedData, true);
            
            Log::info('New merchant key: ' . $decodedData['newMerchantKey']);
            
            // Assuming the decrypted information is an array with the expected keys
            if($environment === 'production'){
                $envObject->setEnvValue('VINNET_MERCHANT_KEY', $decodedData['newMerchantKey']);
                // $envObject->updateEnv([
                //     'VINNET_MERCHANT_KEY' =>  $decodedData['newMerchantKey']
                // ]);
            } else {
                $envObject->setEnvValue('VINNET_MERCHANT_KEY_STAGING', $decodedData['newMerchantKey']);
                // $envObject->updateEnv([
                //     'VINNET_MERCHANT_KEY_STAGING' =>  $decodedData['newMerchantKey']
                // ]);
            }
            
            Log::info("The end for change key.");

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => $decodedData['newMerchantKey']
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * MerchantInfo API: Truy vấn thông tin tài khoản đối tác tại hệ thống Vinnet (deposited / spent / balance).
     * 
     * @param $request
     * 
     * @return Object
     * 
     */
    public function merchantinfo(Request $request, VinnetService $vinnetService)
    {
        try{
            Log::info("Merchant Information");
            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            $tokenData = $vinnetService->authenticate_token();

            $uuid = $vinnetService->generate_formated_uuid();

            $dataRequest = [];

            $reqData = $this->encrypt_data(json_encode($dataRequest));

            $signature = $this->generate_signature(str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            Log::info('Merchant Information [$postData]: ' . json_encode($postData));

            $response = $this->post_vinnet_request(str_replace('"', '', $url) . '/merchantinfo', $tokenData['token'], $postData);

            $decodedResponse = json_decode($response, true);

            if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }

            if (!is_array($decodedResponse)) {
                throw new \Exception('Decoded services data is not an array');
            }

            if($decodedResponse['resCode'] == '00')
            {
                Log::info('Merchant Info: ' . $decodedResponse['resData']);

                $decryptedData = $this->decrypt_data($decodedResponse['resData']);

                Log::info('Decrypted Merchant Info data: ' . $decryptedData);
                
                $decodedData = json_decode($decryptedData, true);

                if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error: ' . json_last_error_msg());
                    throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
                }

                Log::info('Pay item array: ' . print_r($decodedData, true));

                return [
                    'reqUuid' => $uuid,
                    'data' => $decodedData,
                    'code' => (int)$decodedResponse['resCode'],
                    'message' => $decodedResponse['resMesg']
                ];
            } else {
                throw new \Exception($decodedResponse['resMesg']);
            }
        } catch (Exception $e) {
            //Log the exception message
            Log::error('Merchant Information failed: ' . $e->getMessage());

            throw $e;
        }
    }

    public function test_sms(Request $request){
        $apiCMCObject = new APICMCObject();

        $phone_number = $request->input('phone_number');
        $messageCard = $request->input('message');
                    
        $responseSMSData = $apiCMCObject->send_sms($phone_number, $messageCard);
    }
    
}
