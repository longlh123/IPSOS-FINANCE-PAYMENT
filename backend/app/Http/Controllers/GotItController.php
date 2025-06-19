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
use App\Models\ProjectGotIt;
use App\Models\ENVObject;

class GotItController extends Controller
{
    public function get_category(Request $request)
    {
        try{
            $envObject = new ENVObject();

            $header = [
                'X-GI-Authorization: ' . $envObject->env['GOTIT_API_KEY'],
                'Content-Type: application/json'
            ];

            $apiObject = new APIObject('https://api-biz-stg.gotit.vn/api/v3.0/categories', $header);

            $responseData = $apiObject->get_request();

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function check_transaction($interviewURL)
    {
        try
        {
            Log::info('Step 1: Check the project information:');

            $projectQuery = Project::with('projectDetails', 'projectGotIts')
                ->where('internal_code', $interviewURL->internal_code)
                ->where('project_name', $interviewURL->project_name)
                ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                    $query->where('remember_token', $interviewURL->remember_token);
                });
            
            $project = $projectQuery->first();
            
            if(!$project){
                Log::error(Project::STATUS_PROJECT_NOT_FOUND);
                throw new \Exception(Project::STATUS_PROJECT_NOT_FOUND);
            } else {
                //Log::info('Status of project:' . $project->projectDetails->status);

                if($project->projectDetails->status !== Project::STATUS_ON_GOING){
                    Log::error(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                    throw new \Exception(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                } 
            }

            Log::info('Step 2: Check the respondent information:');

            $respondentProcessed = $project->projectGotIts()->where('respondent_id', $interviewURL->respondent_id)->exists();
            
            if($respondentProcessed){
                Log::error(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS.' [Respondent ID]');
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS);
            }

            Log::info('Step 3: Check respondent phone number information:');

            $respPhoneNumberProcessed = $project->projectGotIts()
                ->where(function($query) use ($interviewURL) {
                    $query->where('respondent_phone_number', $interviewURL->respondent_phone_number)
                          ->orWhere('phone_number', $interviewURL->respondent_phone_number);
                })->exists();
            
            if($respPhoneNumberProcessed){
                Log::error(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS.' [Respondent Phone number]');
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS);
            }

            return true;
        }
        catch(\Exception $e)
        {
            throw $e;
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

            if($this->check_transaction($interviewURL))
            {   
                $projectQuery = Project::with('projectDetails', 'projectProvinces')
                    ->where('internal_code', $interviewURL->internal_code)
                    ->where('project_name', $interviewURL->project_name)
                    ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                        $query->where('remember_token', $interviewURL->remember_token);
                    });

                $project = $projectQuery->first();
                
                //Create the respondent with validated data
                $projectGotIt = ProjectGotIt::create([
                    'project_id' => $project->id,
                    'shell_chainid' => $interviewURL->shell_chainid,
                    'respondent_id' => $interviewURL->respondent_id,
                    'employee_id' => $interviewURL->employee->id,
                    'province_id' => $interviewURL->province_id,
                    'interview_start' => $interviewURL->interview_start,
                    'interview_end' => $interviewURL->interview_end,
                    'respondent_phone_number' => $interviewURL->respondent_phone_number,
                    'phone_number' => $interviewURL->respondent_phone_number,
                    'status' => Project::STATUS_REJECT_REASON_PHONE_NUMBER,
                    'reject_message' => $reject_message,
                ]);
    
                // Check if the record was successfully created
                if (!$projectGotIt) {
                    throw new \Exception('Failed to create the transaction record.');
                }

                Log::info('Token stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id, 'reject_message' => $reject_message]);

                return response()->json([
                    'status_code' => Response::HTTP_OK, //200
                    'message' => ProjectGotIt::ERROR_TRANSACTION_DENIED_FEEDBACK_RECORDED
                ], Response::HTTP_OK);
            }
            else
            {   
                return response()->json([
                    'status_code' => Response::HTTP_BAD_REQUEST, //200
                    'message' => 'Unsuccessful request.'
                ], Response::HTTP_BAD_REQUEST);
            }
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

            //Log::info('URL Splitted: ' . json_encode($splittedURL));
            
            $interviewURL = new InterviewURL($splittedURL);

            if($this->check_transaction($interviewURL))
            {
                $projectQuery = Project::with('projectDetails', 'projectGotIts')
                    ->where('internal_code', $interviewURL->internal_code)
                    ->where('project_name', $interviewURL->project_name)
                    ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                        $query->where('remember_token', $interviewURL->remember_token);
                    });
                
                $project = $projectQuery->first();
                
                if(!$project){
                    Log::error(Project::STATUS_PROJECT_NOT_FOUND);
                    throw new \Exception(Project::STATUS_PROJECT_NOT_FOUND);
                } else {
                    //Log::info('Status of project:' . $project->projectDetails->status);

                    if($project->projectDetails->status !== Project::STATUS_ON_GOING){
                        Log::error(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                        throw new \Exception(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                    } 
                }

                Log::info('Step 4: Check phone number information:');

                $phoneNumberProcessed = $project->projectGotIts()
                    ->where(function($query) use ($validatedRequest) {
                        $query->where('respondent_phone_number', $validatedRequest['phone_number'])
                            ->orWhere('phone_number', $validatedRequest['phone_number']);
                    })->exists();
                
                if($phoneNumberProcessed){
                    Log::error(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS.' [Phone number]');
                    throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS);
                }

                Log::info('Step 5: Find price item');

                $price_item = $project->projectProvinces->firstWhere('province_id', $interviewURL->province_id);

                if(!$price_item){
                    Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                    throw new \Exception(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES.' Vui lòng liên hệ Admin để biết thêm thông tin.');
                }

                $price = 0;

                switch($interviewURL->price_level){
                    case 'main':
                        $price = intval($price_item->price_main);
                        break;
                    case 'main_1':
                        $price = intval($price_item->price_main_1);
                        break;
                    case 'main_2':
                        $price = intval($price_item->price_main_2);
                        break;
                    case 'main_3':
                        $price = intval($price_item->price_main_3);
                        break;
                    case 'main_4':
                        $price = intval($price_item->price_main_4);
                        break;
                    case 'main_5':
                        $price = intval($price_item->price_main_5);
                        break;
                    case 'boosters':
                        $price = intval($price_item->price_main);
                        break;
                    case 'boosters_1':
                        $price = intval($price_item->price_main_1);
                        break;
                    case 'boosters_2':
                        $price = intval($price_item->price_main_2);
                        break;
                    case 'boosters_3':
                        $price = intval($price_item->price_main_3);
                        break;
                    case 'boosters_4':
                        $price = intval($price_item->price_main_4);
                        break;
                    case 'boosters_5':
                        $price = intval($price_item->price_main_5);
                        break;
                }

                Log::info('Price: ' . intval($price));

                if($price >= 50000)
                {
                    $responseData = $this->post_voucher('e', $interviewURL, $validatedRequest['phone_number'], $price);
                } 
                else 
                {
                    $responseData = $this->post_voucher('v', $interviewURL, $validatedRequest['phone_number'], $price);
                }
                
                //return response()->json($responseData);

                return response()->json([
                    'status_code' => Response::HTTP_OK, //200
                    'message' => ProjectGotIt::STATUS_SUCCESS,
                ]);
            }            
            else 
            {
                return response()->json([
                    'status_code' => Response::HTTP_BAD_REQUEST, //400
                    'message' => ProjectGotIt::STATUS_ERROR
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }
    
    private function post_voucher($voucher_link_type, $interviewURL, $phone_number, $price)
    {
        try
        {
            $envObject = new ENVObject();

            $header = [
                'X-GI-Authorization: ' . $envObject->env['GOTIT_API_KEY'],
                'Content-Type: application/json'
            ];

            $apiObject = new APIObject('https://api-biz-stg.gotit.vn/api/v3.0/vouchers/'.$voucher_link_type, $header);

            $uuid = $apiObject->generate_formated_uuid();

            $transactionRefId = $envObject->env['GOTIT_TRANSACTIONREFID_PREFIX'] ."_". $uuid;

            Log::info('Transaction RefId: ' . $transactionRefId);

            //[API Key]|transactionRefId
            $signatureData = $envObject->env['GOTIT_API_KEY'] . "|" . $transactionRefId;

            $signature = $apiObject->generate_signature($signatureData);
            
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
            
            //"productId"  => "",

            $dataRequest = [
                "isConvertToCoverLink" => 0,
                "orderName" => 'IPSOS Promotion - ' . date("M Y"),
                "expiryDate" => $expiryDate->format('Y-m-d'),
                "receiver_name" => 'IPSOS Promotion - ' . date("M Y"),
                "transactionRefId" => $transactionRefId,
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
                }
            }

            Log::info('Step 5: Store the information of transaction');
            
            $this->store_gotit_transaction($interviewURL, $dataRequest, ProjectGotIt::STATUS_PENDING_VERIFICATION);

            Log::info('Step 6: Call API');

            $responseData = $apiObject->post_request($dataRequest);

            Log::info($responseData);
            
            if($responseData['statusCode'] === 200)
            {
                if(strlen($responseData['error']) == 0 && strlen($responseData['message']) == 0)
                {
                    Log::info('Step 7: Update the information of voucher');

                    if($voucher_link_type == 'v')
                    {
                        $resultUpdateVoucherData = $this->update_gotit_voucher_link_v($interviewURL, $phone_number, $responseData['data']);
                    }   
                    else 
                    {
                        $resultUpdateVoucherData = $this->update_gotit_voucher_link_e($interviewURL, $phone_number, $responseData['data']);
                    }
                    
                    if($resultUpdateVoucherData)
                    {
                        try
                        {
                            Log::info('Step 8: Send SMS');
                            $voucher_link = ($voucher_link_type == 'v') ? $responseData['data'][0]['vouchers'][0]['voucherLink'] : $responseData['data'][0]['vouchers'][0]['voucher_link'];

                            $responseSMSData = $this->send_sms($voucher_link, $phone_number);
                            
                            if($responseSMSData['statusCode'] === 200)
                            {
                                $resultUpdateStatusSMSData = $this->update_status_of_gotit_sms_sending($interviewURL, $dataRequest, ProjectGotIt::STATUS_SEND_SMS_SUCCESS);

                                if($resultUpdateStatusSMSData)
                                {
                                    Log::info('Step 9: Update the status of transaction');

                                    $resultUpdateStatusTransactionData = $this->update_status_of_gotit_transaction($interviewURL, $dataRequest, ProjectGotIt::STATUS_SUCCESS);

                                    return true;
                                }
                            }
                            else 
                            {
                                $this->update_status_of_gotit_sms_sending($interviewURL, $dataRequest, ProjectGotIt::STATUS_SEND_SMS_FAILED);
                                throw new \Exception($responseSMSData['message']);
                            }
                        }
                        catch(\Exception $e)
                        {
                            $this->update_status_of_gotit_sms_sending($interviewURL, $dataRequest, $e->getMessage());
                            throw new \Exception($e->getMessage());
                        }
                    }
                } 
                else 
                {
                    $status = '';

                    switch(intval($responseData['error']))
                    {
                        case 3:
                            $status = ProjectGotIt::STATUS_NO_PERMISSION; 
                            break;
                        case 2014:
                            $status = ProjectGotIt::STATUS_PRODUCT_NOT_ALLOWED;
                            break;
                        case 2015:
                            $status = ProjectGotIt::STATUS_MIN_VOUCHER_E_VALUE; 
                            break;
                        case 2008:
                            $status = ProjectGotIt::STATUS_TRANSACTION_ALREADY_EXISTS; 
                            break;
                    }

                    $this->update_status_of_gotit_transaction($interviewURL, $dataRequest, $status);
                    throw new \Exception($status);
                }
            } 
            else 
            {
                $this->update_status_of_gotit_transaction($interviewURL, $responseData, 'Lỗi từ phía IPSOS. Chưa xác định được thông tin lỗi');
                throw new \Exception('Lỗi từ phía IPSOS. Chưa xác định được thông tin lỗi');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function send_sms($voucher_link, $phone_number)
    {
        try
        {
            $envObject = new ENVObject();

            $header = [
                'X-GI-Authorization: ' . $envObject->env['GOTIT_API_KEY'],
                'Content-Type: application/json'
            ];

            $apiObject = new APIObject('https://api-biz-stg.gotit.vn/api/v3.0/vouchers/send/sms', $header);

            $dataRequest = [
                "voucherLinkCode" => substr($voucher_link, -8),
                "phoneNo" => $phone_number,
                "receiverNm" => "Got It",
                "senderNm" => "Got It"
            ];
            
            $responseData = $apiObject->post_request($dataRequest);

            Log::info(json_encode($responseData));

            return $responseData;
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            throw new \Exception(ProjectGotIt::STATUS_SEND_SMS_FAILED);
        }
    }
    
    public function store_gotit_transaction($interviewURL, $voucherInfo, $status)
    {
        try
        {
            $projectQuery = Project::with('projectDetails', 'projectGotIts')
                ->where('internal_code', $interviewURL->internal_code)
                ->where('project_name', $interviewURL->project_name)
                ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                    $query->where('remember_token', $interviewURL->remember_token);
                });
            
            $project = $projectQuery->first();
            
            $price = 0;

            if(isset($voucherInfo['amount']))
            {
                $price = $voucherInfo['amount'];
            }
            else 
            {
                switch($voucherInfo['productPriceId'])
                {
                    case 17931:
                        $price = 10000;
                        break;
                    case 3090:
                        $price = 20000;
                        break;
                    case 3555:
                        $price = 30000;
                        break;
                    case 17940:
                        $price = 40000;
                        break;
                }
            }

            //Create the respondent with validated data
            $projectGotIt = ProjectGotIt::create([
                'project_id' => $project->id,
                'shell_chainid' => $interviewURL->shell_chainid,
                'respondent_id' => $interviewURL->respondent_id,
                'employee_id' => $interviewURL->employee->id,
                'province_id' => $interviewURL->province_id,
                'interview_start' => $interviewURL->interview_start,
                'interview_end' => $interviewURL->interview_end,
                'respondent_phone_number' => $interviewURL->respondent_phone_number,
                'phone_number' => $voucherInfo['phone'],
                'transaction_ref_id' => $voucherInfo['transactionRefId'],
                'expiry_date' => $voucherInfo['expiryDate'],
                'order_name' => $voucherInfo['orderName'],
                'amount' => $price,
                'status' => $status
            ]);
            
            // Check if the record was successfully created
            if (!$projectGotIt) {
                throw new \Exception('Failed to create the transaction record.');
            }

            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);
            return true;
        } catch (\Exception $e) {
            // Log the exception message
            Log::error('The status of transaction updating failed: ' . $e->getMessage());
            throw new \Exception('The status of transaction updating failed: ' . $e->getMessage());
        }
    }
    
    public function update_status_of_gotit_transaction($interviewURL, $voucherInfo, $status)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectGotIt::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $voucherInfo['phone']);
            $query->where('transaction_ref_id', $voucherInfo['transactionRefId']);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$voucherInfo[0]['transactionRefId']);
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$voucherInfo[0]['transactionRefId']);
            }

            //Update the status
            $record->status = $status;

            $record->save();

            //Log::info('The status of transaction updating successful.');
            return true;
        } catch(\Exception $e){
            // Log the exception message
            Log::error('The status of transaction updating failed: ' . $e->getMessage());
            throw new \Exception('The status of transaction updating failed: ' . $e->getMessage());
        }
    }

    public function update_status_of_gotit_sms_sending($interviewURL, $voucherInfo, $status)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectGotIt::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $voucherInfo['phone']);
            $query->where('transaction_ref_id', $voucherInfo['transactionRefId']);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$voucherInfo[0]['transactionRefId']);
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$voucherInfo[0]['transactionRefId']);
            }

            //Update the status
            $record->sms_status = $status;

            $record->save();

            Log::info('The status of transaction updating successful.');
            return true;

        } catch(\Exception $e){
            // Log the exception message
            Log::error('The status of transaction updating failed: ' . $e->getMessage());
            throw new \Exception('The status of transaction updating failed: ' . $e->getMessage());
        }
    }

    private function update_gotit_voucher_link_e($interviewURL, $phone_number, $transactionData)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectGotIt::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $phone_number);
            $query->where('transaction_ref_id', $transactionData[0]['transactionRefId']);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionData[0]['transactionRefId']);
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionData[0]['transactionRefId']);
            }

            $record->voucher_status = ProjectGotIt::STATUS_VOUCHER_SUCCESS;
            $record->voucher_link = $transactionData[0]['vouchers'][0]['voucher_link'];
            $record->voucher_link_code = substr($transactionData[0]['vouchers'][0]['voucher_link'], -8);

            if(strlen($transactionData[0]['vouchers'][0]['voucher_cover_link']) > 0)
            {
                $record->voucher_cover_link = $transactionData[0]['vouchers'][0]['voucher_cover_link'];
            }
            
            $record->voucher_serial = $transactionData[0]['vouchers'][0]['voucher_serial']; 
            $record->voucher_value = $transactionData[0]['vouchers'][0]['value'];
            $record->voucher_expired_date = $transactionData[0]['vouchers'][0]['expired_date'];

            $record->save();
            
            Log::info('The information of voucher updating successful.');
            return true;
        }
        catch(\Exception $e)
        {
            Log::error('The information of voucher updating failed: ' . $e->getMessage());
            throw new \Exception('The information of voucher updating failed: ' . $e->getMessage());
        }
    }

    private function update_gotit_voucher_link_v($interviewURL, $phone_number, $transactionData)
    {
        try
        {
            //Find the record and update the status
            $query = ProjectGotIt::query();
            $query->where('shell_chainid', $interviewURL->shell_chainid);
            $query->where('respondent_id', $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $phone_number);
            $query->where('transaction_ref_id', $transactionData[0]['vouchers'][0]['transactionRefId']);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionData[0]['vouchers'][0]['transactionRefId']);
                throw new \Exception(ProjectGotIt::STATUS_TRANSACTION_NOT_EXISTS.': '.$transactionData[0]['vouchers'][0]['transactionRefId']);
            }

            $record->voucher_status = ProjectGotIt::STATUS_VOUCHER_SUCCESS;
            $record->voucher_link = $transactionData[0]['vouchers'][0]['voucherLink'];
            $record->voucher_link_code = $transactionData[0]['vouchers'][0]['voucherLinkCode'];
            $record->voucher_image_link = $transactionData[0]['vouchers'][0]['voucherImageLink'];

            if(strlen($transactionData[0]['vouchers'][0]['voucherCoverLink']) > 0)
            {
                $record->voucher_cover_link = $transactionData[0]['vouchers'][0]['voucherCoverLink'];
            }
            
            $record->voucher_serial = $transactionData[0]['vouchers'][0]['voucherSerial']; 
            $record->voucher_expired_date = $transactionData[0]['vouchers'][0]['expiryDate'];

            $record->voucher_product_id = $transactionData[0]['vouchers'][0]['product']['productId'];
            $record->voucher_price_id = $transactionData[0]['vouchers'][0]['product']['price']['priceId'];
            $record->voucher_value = $transactionData[0]['vouchers'][0]['product']['price']['priceValue'];

            $record->save();

            Log::info('The information of voucher updating successful.');

            return true;
        }
        catch(\Exception $e)
        {
            Log::error('The information of voucher updating failed: ' . $e->getMessage());
            throw new \Exception('The information of voucher updating failed: ' . $e->getMessage());
        }
    }
}