<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreProjectGotItRequest;
use App\Models\APIObject;
use App\Models\InterviewURL;
use App\Models\Project;
use App\Models\ProjectRespondent;
use App\Models\ProjectGotItVoucherTransaction;
use App\Models\ProjectGotItSMSTransaction;
use App\Models\ENVObject;
use App\Models\APICMCObject;
use App\Exceptions\GotItVoucherException;
use App\Constants\SMSStatus;
use App\Constants\TransactionStatus;
use App\Services\ProjectRespondentTokenService;
use App\Http\Requests\TransactionRequest;

class GotItController extends Controller
{
    public function get_categories(Request $request)
    {
        try{
            $apiObject = new APIObject();

            $responseData = $apiObject->get_categories();

            return response()->json($responseData);

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
            $splittedURL = explode("/", $decodedURL);

            $interviewURL = new InterviewURL($splittedURL);

            $project = Project::findByInterviewURL($interviewURL);

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
                'status' => ProjectRespondent::STATUS_RESPONDENT_REJECTED,
                'reject_message' => $reject_message
            ]);

            if (!$projectRespondent) {
                throw new \Exception(ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT);
            }

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id, 'reject_message' => $reject_message]);
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

    public function perform_transaction(TransactionRequest $request, ProjectRespondentTokenService $tokenService)
    {
        try
        {
            $validatedRequest = $request->validated();

            $token = $validatedRequest['token'] ?? null;
            $serviceType = $validatedRequest['service_type'] ?? null;
            $serviceCode = $validatedRequest['service_code'] ?? null;
            $phoneNumber = $validatedRequest['phone_number'] ?? null;
            $provider = $validatedRequest['provider'] ?? null;

            Log::info('Transaction Info: ', [
                'token' => $token,
                'service_type' => $serviceType,
                'service_code' => $serviceCode,
                'phone_number' => $phoneNumber,
                'provider' => $provider
            ]);

            $tokenRecord = $tokenService->verifyToken($token);

            $projectRespondent = $tokenRecord->projectRespondent;
            
            $project = $projectRespondent->project;
            
            Log::info('Project: ' . $project);

            //Tìm thông tin dự án đã được set up giá dựa trên dữ liệu từ Interview URL
            Log::info('Find the price item by province');
            
            try {
                $price = $project->getPriceForProvince($projectRespondent->province_id, $projectRespondent->price_level);
            } catch(\Exception $e){

                Log::error($e->getMessage());

                return response()->json([
                    'status_code' => 903,
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 404);
            }

            if($price == 0)
            {   
                Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                
                return response()->json([
                    'status_code' => 903,
                    'message' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 422);
            }

            Log::info('Price: ' . intval($price));
            
            if($project->projectDetails->status === Project::STATUS_IN_COMING || $project->projectDetails->status === Project::STATUS_ON_HOLD || 
                ($project->projectDetails->status === Project::STATUS_ON_GOING && $projectRespondent->employee_id === 1)){
                    
                    Log::info('Staging Environment: ');

                    $tokenRecord->update([
                        'status' => 'blocked'
                    ]);
                    
                    return response()->json([
                        'status_code' => 900,
                        'message' => TransactionStatus::STATUS_TRANSACTION_TEST . ' [Giá trị quà tặng: ' . $price . ']'
                    ], 200);
            } 

            Log::info('Live Environment:');

            try
            {
                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $phoneNumber);

            } catch(\Exception $e){
                return response()->json([
                    'status_code' => 905,
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => $e->getMessage()
                ], 409);
            }

            $projectRespondent->update([
                'phone_number' => $phoneNumber,
                'service_code' => $serviceCode,
                'service_type' => $serviceType,
                'provider' => $provider
            ]); 

            Log::info('Project respondent: ' . json_encode($projectRespondent->toArray()));
            
            //Tìm loại voucher tương ứng với mức giá quà tặng
            $priceMap = [
                3088 => 10000,
                3090 => 20000,
                3555 => 30000,
                17940 => 40000,
                2991 => 50000,
                6832 => 60000,
                9369 => 70000,
                9343 => 80000,
                6546 => 90000,
                2992 => 100000,
                2993 => 200000,
                6440 => 300000,
                6960 => 400000,
                2994 => 500000
            ];

            $prices = array();

            foreach($priceMap as $price_id => $p){
                array_push($prices, $p);
            }

            $prices = array();
            $selectedPrices = array();

            foreach($priceMap as $price_id => $p){
                array_push($prices, $p);
            }

            if(in_array($price, $prices)){
                array_push($selectedPrices, $price);
            } else {
                $selectedPrices = $this->findSubsets($prices, $price);
            }
            
            Log::info("Mệnh giá tiền cần chuyển cho đáp viên: " . print_r($selectedPrices, true));

            if(count($selectedPrices) == 1){
                $voucher_link_type = 'v'; 
            } else {
                if(count($selectedPrices) >= 2 && count($selectedPrices) <= 4){
                    $voucher_link_type = 'g'; 
                } else {
                    $voucher_link_type = 'e';

                    $selectedPrices = array();
                    array_push($selectedPrices, $price);
                }  
            }

            Log::info('Call API GotIt');
            $apiObject = new APIObject();
            
            $voucherRequest = $this->generate_voucher_request($apiObject, $voucher_link_type, $phoneNumber, $selectedPrices);
            
            try{
                
                $responsedVoucher = $apiObject->get_vouchers($voucher_link_type, $voucherRequest);
            }catch(\Exception $e){

                Log::error("GotIt API Error: " . $e->getMessage());
                
                if(isset($projectRespondent)){

                    $failedVoucherTransaction = $projectRespondent->createGotitVoucherTransactions([
                        'project_respondent_id'    => $projectRespondent->id,
                        'transaction_ref_id'       => $voucherRequest['transactionRefId'],
                        'transaction_ref_id_order' => 1,
                        'expiry_date'              => $voucherRequest['expiryDate'],
                        'order_name'               => $voucherRequest['orderName'],
                        'amount'                   => $price,
                        'voucher_status'           => TransactionStatus::STATUS_ERROR . '[' . $e->getMessage() . ']'
                    ]);
                    
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_API_FAILED);
                }

                return response()->json([
                    'status_code' => 998,
                    'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                    'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                ], 404);
            }

            Log::info('Responsed Voucher: ' . json_encode($responsedVoucher));
            
            $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_WAITING_FOR_GIFT);

            Log::info('Store the information of transaction.');

            $messagesToSend = [];
            $expiredDate = "";

            if($voucher_link_type === 'e'){

                $voucherData = $responsedVoucher['vouchers'][0];
                
                $voucherTransaction = $projectRespondent->createGotitVoucherTransactions([
                    'project_respondent_id' => $projectRespondent->id,
                    'transaction_ref_id' => $voucherRequest['transactionRefId'],
                    'transaction_ref_id_order' => 1,
                    'expiry_date' => $voucherRequest['expiryDate'],
                    'order_name' => $voucherRequest['orderName'],
                    'amount' => $voucherRequest['amount'],
                    'voucher_link' => $voucherData['voucher_link'],
                    'voucher_link_code' => substr($voucherData['voucher_link'], -8),
                    'voucher_serial' => $voucherData['voucher_serial'],
                    'voucher_value' => $voucherData['value'],
                    'voucher_expired_date' => $voucherData['expired_date'],
                    'voucher_status' => ProjectGotItVoucherTransaction::STATUS_VOUCHER_SUCCESS
                ]);

                $messagesToSend[] = sprintf(
                    "IPSOS cam on ban tham gia chia se, IPSOS tang ban phan qua tri gia %svnd. Mo qua tai: %s HSD %s",
                    $price ?? 'N/A',
                    $voucherData['voucher_link'] ?? 'N/A',
                    $voucherData['expired_date'] ?? 'N/A'
                );

            } else if ($voucher_link_type === 'v') {
                $voucherData = $responsedVoucher['vouchers'][0];

                $amount = $priceMap[$voucherRequest['productPriceId']]
                            ?? throw new \Exception('Giá trị ' . $voucherRequest['productPriceId'] . ' chưa được định nghĩa mức giá.');
                
                $voucherTransaction = $projectRespondent->createGotitVoucherTransactions([
                    'project_respondent_id' => $projectRespondent->id,
                    'transaction_ref_id' => $voucherRequest['transactionRefId'],
                    'transaction_ref_id_order' => 1,
                    'expiry_date' => $voucherRequest['expiryDate'],
                    'order_name' => $voucherRequest['orderName'],
                    'amount' => $amount,
                    'voucher_code' => $voucherData['voucherCode'],
                    'voucher_link' => $voucherData['voucherLink'],
                    'voucher_serial' => $voucherData['voucherSerial'],
                    'voucher_value' => $voucherData['product']['price']['priceValue'],
                    'voucher_expired_date' => $voucherData['expiryDate'],
                    'voucher_product_id' => $voucherRequest['productId'],
                    'voucher_price_id' => $voucherRequest['productPriceId'],
                    'voucher_status' => ProjectGotItVoucherTransaction::STATUS_VOUCHER_SUCCESS
                ]);

                $messagesToSend[] = sprintf(
                    "IPSOS cam on ban tham gia chia se, IPSOS tang ban phan qua tri gia %svnd. Mo qua tai: %s HSD %s",
                    $price ?? 'N/A',
                    $voucherData['voucherLink'] ?? 'N/A',
                    $voucherData['expiryDate'] ?? 'N/A'
                );
            } else {
                foreach($responsedVoucher['vouchers'] as $index => $voucher){

                    $amount = $priceMap[$voucher['price_id']]
                            ?? throw new \Exception('Giá trị ' . $voucher['price_id'] . ' chưa được định nghĩa mức giá.');
                    
                    $voucherData = [
                        'project_respondent_id' => $projectRespondent->id,
                        'transaction_ref_id' => $apiObject->getTransactionRefId(),
                        'transaction_ref_id_order' => $index + 1,
                        'expiry_date' => $voucherRequest['expiryDate'],
                        'order_name' => $voucherRequest['orderName'],
                        'amount' => $amount,
                        'voucher_link_group' => $responsedVoucher['groupVouchers']['voucherLink'],
                        'voucher_link_code_group' => $responsedVoucher['groupVouchers']['voucherLinkCode'],
                        'voucher_serial_group' => $responsedVoucher['groupVouchers']['voucherSerial'],
                        'voucher_code' => $voucher['code'],
                        'voucher_link' => $voucher['link'],
                        'voucher_link_code' => substr($voucher['link'], -8),
                        'voucher_serial' => $voucher['serial'],
                        'voucher_expired_date' => $voucher['expired_date'],
                        'voucher_product_id' => $voucher['product_id'],
                        'voucher_price_id' => $voucher['price_id'],
                        'voucher_value' => $voucher['value'],
                        'voucher_status' => ProjectGotItVoucherTransaction::STATUS_VOUCHER_SUCCESS
                    ];

                    $voucherTransaction = $projectRespondent->createGotitVoucherTransactions($voucherData);

                    $expiredDate = $voucher['expired_date'];
                }

                $messagesToSend[] = sprintf(
                    "IPSOS cam on ban tham gia chia se, IPSOS tang ban phan qua tri gia %svnd. Mo qua tai: %s HSD %s",
                    $price ?? 'N/A',
                    $responsedVoucher['groupVouchers']['voucherLink'] ?? 'N/A',
                    $expiredDate ?? 'N/A'
                );
            }

            Log::info('Generate a SMS request');
            
            $smsTransaction = $voucherTransaction->createGotitSMSTransaction([
                'voucher_transaction_id' => $voucherTransaction->id,
                'transaction_ref_id' => $apiObject->getTransactionRefId(),
                'sms_status' => SMSStatus::PENDING
            ]);

            $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_DISPATCHED);

            $projectRespondent->token()->update([
                'status' => 'blocked'
            ]);

            if(!empty($messagesToSend)){
            
                $messageCard = sprintf(
                    "%s",
                    implode("\n", $messagesToSend) ?? 'N/A'
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
                        'status_code' => 999,
                        'transaction_ref_id' => $apiObject->getTransactionRefId(),
                        'message' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM,
                        'error' => ProjectRespondent::ERROR_RESPONDENT_GIFT_SYSTEM
                    ], 404);
                }

                if(intval($responseSMSData['status']) == 1){
                    $smsTransactionStatus = $smsTransaction->updateStatus(SMSStatus::SUCCESS, intval($responseSMSData['countSms']));

                    $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED);

                    return response()->json([
                        'status_code' => 900,
                        'transaction_ref_id' => $apiObject->getTransactionRefId(),
                        'message' => TransactionStatus::SUCCESS
                    ], 200);
                } else {
                    $smsTransactionStatus = $smsTransaction->updateStatus($responseSMSData['statusDescription'], 0);

                    $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_NOT_RECEIVED);

                    Log::error(SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']');

                    return response()->json([
                        'status_code' => 997,
                        'transaction_ref_id' => $apiObject->getTransactionRefId(),
                        'message' => SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']',
                        'error' => SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']',
                    ], 400);
                }
            } else {

                $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_NOT_RECEIVED);

                Log::error(SMSStatus::ERROR_C98);

                return response()->json([
                    'status_code' => 997,
                    'transaction_ref_id' => $apiObject->getTransactionRefId(),
                    'message' => TransactionStatus::ERROR_C98,
                    'error' => TransactionStatus::ERROR_C98,
                ], 400);
            }
        }
        catch (GotItVoucherException $e){
            Log::error($e->getLogContext());

            if($projectRespondent){
                $projectRespondent->updateStatus($e->getUserMessage());
            }

            return response()->json([
                'status_code' => $e->getCode(),
                'message' => $e->getUserMessage(),
            ]);
        }
        catch (\Exception $e) {
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function generate_voucher_request($apiObject, $voucher_link_type, $phone_number, $prices)
    {
        $time=strtotime(date('Y-m-d'));
        $month = date("F", $time);
        $year = date("Y", $time);

        if ($month < 6){
            $month = 12;
        } else {
            $month = 6;
            $year += 1;
        }
        
        $expiryDate =  new \DateTime("{$year}-{$month}-01");
        $orderName = 'IPSOS Promotion - ' . date("M Y");

        $apiObject->setTransactionRefId();
        $apiObject->setSignatureData("VOUCHER " . strtoupper($voucher_link_type), $orderName, $expiryDate->format('Y-m-d'));

        $signature = $apiObject->generate_signature();

        $priceMap = [
            3088 => 10000,
            3090 => 20000,
            3555 => 30000,
            17940 => 40000,
            2991 => 50000,
            6832 => 60000,
            9369 => 70000,
            9343 => 80000,
            6546 => 90000,
            2992 => 100000,
            2993 => 200000,
            6440 => 300000,
            6960 => 400000,
            2994 => 500000
        ];

        if($voucher_link_type === 'e' || $voucher_link_type === 'v'){
            $dataRequest = [
                "isConvertToCoverLink" => 0,
                "orderName" => $orderName,
                "expiryDate" => $expiryDate->format('Y-m-d'),
                "receiver_name" => $orderName,
                "transactionRefId" => $apiObject->getTransactionRefId(),
                "use_otp" => 0,
                // "otp_type" => 1,    
                "phone" => $phone_number,
            ];

            if($voucher_link_type === 'e')
            {
                $dataRequest["amount"] = $prices[0];
                $dataRequest["signature"] = $signature;
            } 

            if($voucher_link_type === 'v')
            {
                $dataRequest['quantity'] = 1;
                $dataRequest['productId'] = 1541;

                foreach($priceMap as $price_id => $p){
                    if($prices[0] === $p){
                        $dataRequest['productPriceId'] = $price_id;
                        break;
                    }
                }

                $dataRequest["signature"] = $signature;
            }
        } else {
            $projectList = [];

            foreach($prices as $price){
                foreach($priceMap as $price_id => $p){
                    if($price === $p){
                        $projectList[] = [
                            "productId" => 1541,
                            "productPriceId" => $price_id,
                            "quantity" => 1
                        ];
                        break;
                    }
                }
            }

            $dataRequest = [
                "productList" => $projectList,
                "orderName" => $orderName,
                "expiryDate" => $expiryDate->format('Y-m-d'),
                "receiver_name" => $orderName,
                "transactionRefId" => $apiObject->getTransactionRefId(),
                "use_otp" => 0,
                // "otp_type" => 1,    
                "phone" => $phone_number,
            ];

            $dataRequest["signature"] = $signature;
        }

        Log::info('Voucher Request: ' . json_encode($dataRequest));

        return $dataRequest;
    }
    
    private function generate_sms_request($apiObject, $voucher_link, $phone_number)
    {
        $apiObject->setTransactionRefId();
        $apiObject->setSignatureData('SMS', null, null);

        $signature = $apiObject->generate_signature();

        $dataRequest = [
            "voucherLinkCode" => substr($voucher_link, -8),
            "phoneNo" => $phone_number,
            "receiverNm" => "Got It",
            "senderNm" => "Got It",
            "signature" => $signature
        ];

        Log::info('SMS Request: ' . json_encode($dataRequest));

        return $dataRequest;
    }

    public function check_transaction(Request $request, $transactionRefId){

        try{
            Log::info("Check Transaction RefId: " . $transactionRefId);

            $apiObject = new APIObject();

            $responsedVoucher = $apiObject->check_transaction_refid($transactionRefId);

            Log::info("Check Transaction: " . json_encode($responsedVoucher));

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => $responsedVoucher
            ]);
            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function update_gotit_voucher_link_v($interviewURL, $phone_number, $transactionRefId, $voucherData)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectRespondent::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $phone_number);
            $query->where('transaction_ref_id', $transactionRefId);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning(ProjectRespondent::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionRefId);
                throw new \Exception(ProjectRespondent::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionRefId);
            }

            $record->voucher_status = ProjectRespondent::STATUS_VOUCHER_SUCCESS;
            $record->voucher_link = $voucherData['voucherLink'];
            $record->voucher_link_code = $voucherData['voucherLinkCode'];
            $record->voucher_image_link = $voucherData['voucherImageLink'];

            if(strlen($voucherData['voucherCoverLink']) > 0)
            {
                $record->voucher_cover_link = $voucherData['voucherCoverLink'];
            }
            
            $record->voucher_serial = $voucherData['voucherSerial']; 
            $record->voucher_expired_date = $voucherData['expiryDate'];

            $record->voucher_product_id = $voucherData['product']['productId'];
            $record->voucher_price_id = $voucherData['product']['price']['priceId'];
            $record->voucher_value = $voucherData['product']['price']['priceValue'];

            $record->save();

            Log::info('The information of voucher updating successful.');
        }
        catch(\Exception $e)
        {
            Log::error('The information of voucher updating failed: ' . $e->getMessage());
            throw new \Exception('The information of voucher updating failed: ' . $e->getMessage());
        }
    }
}