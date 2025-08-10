<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreProjectGotItRequest;
use App\Models\APIObject;
use App\Models\InterviewURL;
use App\Models\Project;
use App\Models\ProjectRespondent;
use App\Models\ProjectGotItVoucherTransaction;
use App\Models\ProjectGotItSMSTransaction;
use App\Models\ENVObject;

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
                'respondent_id' => $interviewURL->respondent_id,
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

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id, 'reject_message' => $reject_message]);
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

            $splittedURL = explode("/", $decodedURL);

            $interviewURL = new InterviewURL($splittedURL);

            Log::info('Project respondent');

            $project = Project::findByInterviewURL($interviewURL);
            
            ProjectRespondent::checkIfRespondentProcessed($project, $interviewURL);

            Log::info('Store the Project Respondent');

            $projectRespondent = $project->createProjectRespondents([
                'project_id' => $project->id,
                'shell_chainid' => $interviewURL->shell_chainid,
                'respondent_id' => $interviewURL->respondent_id,
                'employee_id' => $interviewURL->employee->id,
                'province_id' => $interviewURL->province_id,
                'interview_start' => $interviewURL->interview_start,
                'interview_end' => $interviewURL->interview_end,
                'respondent_phone_number' => $interviewURL->respondent_phone_number,
                'phone_number' => $validatedRequest['phone_number'],
                'price_level' => $interviewURL->price_level,
                'channel' => $interviewURL->channel,
                'status' => ProjectRespondent::STATUS_RESPONDENT_PENDING
            ]);

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);

            if (!$projectRespondent) {
                throw new \Exception(ProjectRespondent::ERROR_CANNOT_STORE_RESPONDENT);
            }
            
            Log::info('Project respondent: ' . $projectRespondent);

            Log::info('Find the price item by province');
            
            $price = $project->getPriceForProvince($interviewURL);
 
            Log::info('Price: ' . intval($price));

            if($price >= 50000)
            { 
                $voucher_link_type = 'e';
            }
            else 
            {
                $voucher_link_type = 'v';
            }

            Log::info('Generate a voucher request');
            $apiObject = new APIObject();
            
            $voucherRequest = $this->generate_voucher_request($apiObject, $voucher_link_type, $interviewURL, $validatedRequest['phone_number'], $price);
            
            $voucherTransaction = $projectRespondent->createGotitVoucherTransactions([
                'project_respondent_id' => $projectRespondent->id,
                'transaction_ref_id' => $voucherRequest['transactionRefId'],
                'expiry_date' => $voucherRequest['expiryDate'],
                'order_name' => $voucherRequest['orderName'],
                'amount' => $voucherRequest['amount'],
                'voucher_status' => ProjectGotItVoucherTransaction::STATUS_PENDING_VERIFICATION
            ]);

            $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_WAITING_FOR_GIFT);
            
            Log::info('Call API');

            $responsedVoucher = $apiObject->get_vouchers($voucher_link_type, $voucherRequest);

            Log::info('Responsed Voucher: ' . json_encode($responsedVoucher));

            $updateVoucherTransactionStatus = $voucherTransaction->updateGotitVoucherTransaction($responsedVoucher['vouchers'][0]);

            Log::info('Generate a SMS request');

            $smsRequest = $this->generate_sms_request($apiObject, $voucherTransaction->voucher_link, $validatedRequest['phone_number']);

            $smsTransaction = $voucherTransaction->createGotitSMSTransaction([
                'voucher_transaction_id' => $voucherTransaction->id,
                'transaction_ref_id' => $apiObject->getTransactionRefId(),
                'sms_status' => ProjectGotItSMSTransaction::STATUS_SMS_PENDING
            ]);

            $updateRespondentStatus = $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_DISPATCHED);
            
            $responseSMSData = $apiObject->send_sms($smsRequest);


            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED,
            ]);
            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function generate_voucher_request($apiObject, $voucher_link_type, $interviewURL, $phone_number, $price)
    {
        $signature = $apiObject->generate_signature("|");
        
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

        $dataRequest = [
            "isConvertToCoverLink" => 0,
            "orderName" => $orderName,
            "expiryDate" => $expiryDate->format('Y-m-d'),
            "receiver_name" => $orderName,
            "transactionRefId" => $apiObject->getTransactionRefId(),
            "use_otp" => 1,
            "otp_type" => 1,    
            "phone" => $phone_number,
        ];

        if($voucher_link_type === 'e')
        {
            $dataRequest["amount"] = $price;
            $dataRequest["signature"] = $signature;
        } 

        if($voucher_link_type === 'v')
        {
            $dataRequest['quantity'] = 1;
            $dataRequest['productId'] = 1541;

            switch($price)
            {
                case 10000:
                    $dataRequest['productPriceId'] = 17931;
                    break;
                case 20000:
                    $dataRequest['productPriceId'] = 3090;
                    break;
                case 30000:
                    $dataRequest['productPriceId'] = 3555;
                    break;
                case 40000:
                    $dataRequest['productPriceId'] = 17940;
                    break;
                default:
                    throw new \Exception('Giá trị $price không hợp lệ cho productPriceId');
            }
        }

        Log::info('Voucher Request: ' . json_encode($dataRequest));

        return $dataRequest;
    }
    
    private function generate_sms_request($apiObject, $voucher_link, $phone_number)
    {
        $signature = $apiObject->generate_signature("|||");

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

    private function update_gotit_voucher_link_v($interviewURL, $phone_number, $transactionRefId, $voucherData)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectRespondent::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->respondent_id);
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

    // public static function storeGotitVoucherransaction($interviewURL, $voucherInfo, $status)
    // {
    //     try
    //     {
    //         Log::info('Store gotit transaction: ' . json_encode($voucherInfo));

    //         $projectQuery = Project::with('projectDetails', 'projectRespondents')
    //             ->where('internal_code', $interviewURL->internal_code)
    //             ->where('project_name', $interviewURL->project_name)
    //             ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
    //                 $query->where('remember_token', $interviewURL->remember_token);
    //             });
            
    //         $project = $projectQuery->first();
            
    //         $price = 0;

    //         if(isset($voucherInfo['amount']))
    //         {
    //             $price = $voucherInfo['amount'];
    //         }
    //         else 
    //         {
    //             switch($voucherInfo['productPriceId'])
    //             {
    //                 case 17931:
    //                     $price = 10000;
    //                     break;
    //                 case 3090:
    //                     $price = 20000;
    //                     break;
    //                 case 3555:
    //                     $price = 30000;
    //                     break;
    //                 case 17940:
    //                     $price = 40000;
    //                     break;
    //             }
    //         }

    //         //Create the respondent with validated data
    //         $projectRespondent = ProjectRespondent::create([
    //             'project_id' => $project->id,
    //             'shell_chainid' => $interviewURL->shell_chainid,
    //             'respondent_id' => $interviewURL->respondent_id,
    //             'employee_id' => $interviewURL->employee->id,
    //             'province_id' => $interviewURL->province_id,
    //             'interview_start' => $interviewURL->interview_start,
    //             'interview_end' => $interviewURL->interview_end,
    //             'respondent_phone_number' => $interviewURL->respondent_phone_number,
    //             'phone_number' => $voucherInfo['phone'],
    //             'transaction_ref_id' => $voucherInfo['transactionRefId'],
    //             'expiry_date' => $voucherInfo['expiryDate'],
    //             'order_name' => $voucherInfo['orderName'],
    //             'amount' => $price,
    //             'status' => $status
    //         ]);
            
    //         // Check if the record was successfully created
    //         if (!$projectRespondent) {
    //             throw new \Exception('Failed to create the transaction record.');
    //         }

    //         Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);
    //         return true;
    //     } catch (\Exception $e) {
    //         // Log the exception message
    //         Log::error('The status of transaction updating failed: ' . $e->getMessage());
    //         throw new \Exception('The status of transaction updating failed: ' . $e->getMessage());
    //     }
    // }
}