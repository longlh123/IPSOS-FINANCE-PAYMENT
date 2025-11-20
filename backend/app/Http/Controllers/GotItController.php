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

    public function perform_transaction(StoreProjectGotItRequest $request)
    {
        try
        {
            $validatedRequest = $request->validated();

            $decodedURL = base64_decode($validatedRequest['url']);

            if (!$decodedURL || !str_contains($decodedURL, '/')) {
                return response()->json([
                    'message' => 'URL không hợp lệ.',
                    'error' => 'INVALID_URL'
                ], 400);
            }

            $splittedURL = explode("/", $decodedURL);

            try{

                $interviewURL = new InterviewURL($splittedURL);

                if($interviewURL->channel != 'gotit'){
                    Log::error('URL Got It nhưng thông tin đường link là quà Vinnet.');
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

            //Tìm thông tin dự án đã được set up giá dựa trên dữ liệu từ Interview URL
            Log::info('Find the price item by province');
            
            try {
                $price = $project->getPriceForProvince($interviewURL);
            } catch(\Exception $e){

                Log::error($e->getMessage());

                return response()->json([
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 404);
            }

            Log::info('Price: ' . intval($price));

            if($project->projectDetails->status === Project::STATUS_IN_COMING || $project->projectDetails->status === Project::STATUS_ON_HOLD || 
                ($project->projectDetails->status === Project::STATUS_ON_GOING && !in_array(substr(strtolower($interviewURL->location_id), 0, 2), ['hn', 'sg', 'dn', 'ct']))){
                
                    Log::info('Staging Environment: ');
                
                    return response()->json([
                        'message' => TransactionStatus::STATUS_TRANSACTION_TEST . ' [Giá trị quà tặng: ' . $price . ']'
                    ], Response::HTTP_OK);
            } 
            
            Log::info('Live Environment:');
            
            if($price == 0)
            {   
                Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                
                return response()->json([
                    'message' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => Project::STATUS_PROJECT_NOT_SUITABLE_PRICES
                ], 404);
            }

            try
            {
                //Kiểm tra đáp viên đã thực hiện giao dịch nhận quà trước đó hay chưa?
                ProjectRespondent::checkIfRespondentProcessed($project, $interviewURL);

                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $validatedRequest['phone_number']);

            } catch(\Exception $e){
                return response()->json([
                    'message' => $e->getMessage() . ' Vui lòng liên hệ Admin để biết thêm thông tin.',
                    'error' => $e->getMessage()
                ], 409);
            }

            // Tìm thông tin của Project Respondent
            $projectRespondent = ProjectRespondent::findProjectRespondent($project, $interviewURL);

            if(!$projectRespondent){

                DB::beginTransaction();

                try{
                    //Tạo Project Respondent
                    $projectRespondent = $project->createProjectRespondents([
                        'project_id' => $project->id,
                        'shell_chainid' => $interviewURL->shell_chainid,
                        'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id,
                        'employee_id' => $interviewURL->employee->id,
                        'province_id' => $interviewURL->province_id,
                        'interview_start' => $interviewURL->interview_start,
                        'interview_end' => $interviewURL->interview_end,
                        'respondent_phone_number' => $interviewURL->respondent_phone_number,
                        'phone_number' => $validatedRequest['phone_number'],
                        'service_code' => $validatedRequest['service_code'],
                        'price_level' => $interviewURL->price_level,
                        'channel' => $interviewURL->channel,
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
                Log::info("Number of transactions: " . $projectRespondent->gotitVoucherTransactions()->count());
                
                if($projectRespondent->gotitVoucherTransactions()->count() == 0){

                    $projectRespondent->update([
                        'phone_number' => $validatedRequest['phone_number'],
                        'service_code' => $validatedRequest['service_code'],
                    ]);

                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_PENDING);
                } else {

                    //Nếu đã thực hiện giao dịch => không cho thực hiện
                    return response()->json([
                        'message' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT,
                        'error' => ProjectRespondent::ERROR_DUPLICATE_RESPONDENT . ' [Trường hợp Đáp viên đã tồn tại và đã có thực hiện giao dịch]'
                    ], 500);
                }
            }

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id]);

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
            
            $voucherRequest = $this->generate_voucher_request($apiObject, $voucher_link_type, $interviewURL, $validatedRequest['phone_number'], $selectedPrices);
            
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
                    "%s: Link:%s",
                    number_format($voucherData['value'] / 1000, 0) . 'K',
                    $voucherData['voucher_link'] ?? 'N/A'
                );

                $expiredDate = $voucherData['expired_date'];

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
                    "%s: Code:%s,Seri:%s",
                    number_format($voucherData['product']['price']['priceValue'] / 1000, 0) . 'K',
                    $voucherData['voucherCode'] ?? 'N/A',
                    $voucherData['voucherSerial'] ?? 'N/A'
                );

                $expiredDate = $voucherData['expiryDate'];
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

                    $messagesToSend[] = sprintf(
                        "%s: Code:%s,Seri:%s",
                        number_format($voucher['value'] / 1000, 0) . 'K',
                        $voucher['code'] ?? 'N/A',
                        $voucher['serial'] ?? 'N/A'
                    );

                    $expiredDate = $voucher['expired_date'];
                }
            }

            Log::info('Generate a SMS request');
            
            $smsTransaction = $voucherTransaction->createGotitSMSTransaction([
                'voucher_transaction_id' => $voucherTransaction->id,
                'transaction_ref_id' => $apiObject->getTransactionRefId(),
                'sms_status' => SMSStatus::PENDING
            ]);

            $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_DISPATCHED);

            if(!empty($messagesToSend)){
            
                $messageCard = sprintf(
                    "IPSOS cam on ban. Tang ban qua GotIt:\n%s\nExp:%s",
                    implode("\n", $messagesToSend) ?? 'N/A',
                    $expiredDate ?? 'N/A'
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

                    $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED);

                    return response()->json([
                        'message' => TransactionStatus::SUCCESS
                    ], 200);
                } else {
                    $smsTransactionStatus = $smsTransaction->updateStatus($responseSMSData['statusDescription'], 0);

                    $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_NOT_RECEIVED);

                    Log::error(SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']');

                    return response()->json([
                        'message' => SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']',
                        'error' => SMSStatus::ERROR . ' [' . $responseSMSData['statusDescription'] . ']',
                    ], 400);
                }
            } else {

                $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_NOT_RECEIVED);

                Log::error(SMSStatus::ERROR_C98);

                return response()->json([
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
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function generate_voucher_request($apiObject, $voucher_link_type, $interviewURL, $phone_number, $prices)
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