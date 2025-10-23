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
use App\Http\Requests\StoreProjectVinnetTokenRequest;
use App\Http\Resources\VinnetProjectResource;

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

    private function generate_formated_uuid()
    {
        // Generate a UUID using Laravel's Str::uuid() method
        $uuid = Uuid::uuid4()->toString();
        return $uuid;
    }

    function json_validator($data) 
    { 
        if (!empty($data)) { 
            return is_string($data) &&  
              is_array(json_decode($data, true)) ? true : false; 
        } 
        return false; 
    } 

    private function encrypt_data($data)
    {
        try {
            // Log::info('Starting for encrypt data');

            // Log::info('Data to encrypt: ' . $data);
            $envObject = new ENVObject();
            
            // Load the public key from the file
            $publicKeyPath = storage_path('keys/vinnet/' . $envObject->environment . '/vinnet_public_key.pem');
            $publicKey = file_get_contents($publicKeyPath);

            // Check if the public key was successfully loaded
            if ($publicKey === false) {
                Log::error('Failed to load public key from path: ' . $publicKeyPath);
                throw new Exception('Failed to load public key from path: ' . $publicKeyPath);
            }

            // Get a public key resource
            $pubKeyId = openssl_get_publickey($publicKey);

            // Check if the key resource is valid
            if (!$pubKeyId) {
                Log::error('Public key is not valid');
                throw new Exception('Public key is not valid');
            }

            $encryptedData = '';
            $encryptionSuccess = openssl_public_encrypt($data, $encryptedData, $pubKeyId);

            // Free the key resource
            openssl_free_key($pubKeyId);

            if (!$encryptionSuccess) {
                Log::error('Encryption failed');
                throw new Exception('Encryption failed');
            }

            // Log::info('Encrypted Data: ' . $encryptedData);

            // Encode the encrypted data to base64
            $encodedData = base64_encode($encryptedData);

            // Log::info("Encode the encrypted data: " . $encodedData);

            // Log::info('The end for encrypt data');

            return $encodedData;
        } catch(Exception $e) {
            // Log the exception message
            Log::error('Data encryption failed: ' . $e->getMessage());

            // Rethrow the exception to be handled by the calling code
            throw $e;
        }
    }

    /**
     * Encrypt data
     * 
     * @param $encrypted_data
     * 
     * @return string
     * 
     */
    private function decrypt_data($encrypted_data)
    {  
        try{
            // Log the encrypted data
            // Log::info('Encrypted data: ' . $encrypted_data);
            $envObject = new ENVObject();

            $privateKey = file_get_contents(storage_path('keys/vinnet/' . $envObject->environment . '/private_key.pem'));

            // Base64 decode the encrypted data
            $decodedEncryptedData = base64_decode($encrypted_data);
            
            if ($decodedEncryptedData === false) {
                Log::error('Base64 decoding failed');
                throw new Exception('Base64 decoding failed');
            }

            // Log::info('Base64 decoding data: ' . $decodedEncryptedData);

            // Decrypt the data
            $decryptedData = '';
            
            $decryptionSuccess = openssl_private_decrypt($decodedEncryptedData, $decryptedData, $privateKey);
            
            if (!$decryptionSuccess) {
                Log::error('Decryption failed');
                throw new Exception('Decryption failed');
            }

            // Log::info('Data decryption successfully: ' . $decryptedData);

            // Validate UTF-8 encoding
            if (!mb_check_encoding($decryptedData, 'UTF-8')) {
                // Clean invalid characters
                $decryptedData = mb_convert_encoding($decryptedData, 'UTF-8', 'UTF-8');
            }

            // Clean the decrypted data (remove control characters)
            $cleanData = preg_replace('/[[:cntrl:]]/', '', $decryptedData);

            // Try removing additional characters (adjust based on your needs)
            $cleanData = trim($cleanData);
            
            // Log::info('Clean data: ' . $cleanData);
            
            // Decode the clean data as JSON
            $decodedData = json_decode($cleanData, true);

            // Log the JSON decoded data
            //Log::info('JSON validator: ' . json_encode($decodedData));
            
            // Check if 'newMerchantKey' is present
            if (!isset($decodedData['newMerchantKey']) && !isset($decodedData['token']) && !(!isset($decodedServicesData['serviceItems']) || !is_array($decodedServicesData['serviceItems']))) {
                // Log::error('newMerchantKey key is missing');
                // throw new \Exception('newMerchantKey key is missing');

                // Validate the decrypted JSON
                if(!$this->json_validator($cleanData)){
                    $decodedData = json_decode($cleanData, true);
                
                    if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Malformed JSON data: ' . json_last_error_msg());
                        throw new Exception('Malformed JSON data: ' . json_last_error_msg());
                    }
                } else {
                    $decodedData = $cleanData;
                }
            } else {
                $decodedData = $cleanData;
            }
            
            // Log::info('Result data decryption: ' . $decodedData);
            
            return json_encode($decodedData);
        } catch (Exception $e){
            // Log the exception message
            Log::error('Data decryption failed: ' . $e->getMessage());

            // Rethrow the exception to be handled by the calling code
            throw $e;
        }
    }

    private function generate_signature($data)
    {
        try {
            // Log::info('Starting for generating signature');

            // Log::info('Data to generate Signature: ' . $data);
            $envObject = new ENVObject();

            // Load the public key from the file
            $privateKeyPath = storage_path('keys/vinnet/' . $envObject->environment . '/private_key.pem');
            $privateKey = file_get_contents($privateKeyPath);

            // Check if the private key was successfully loaded
            if ($privateKey === false) {
                throw new Exception('Failed to load private key');
            }

            // Get a private key resource
            $privateKeyId = openssl_get_privatekey($privateKey);

            // Check if the key resource is valid
            if (!$privateKeyId) {
                Log::error('Private key is not valid');
                throw new Exception('Private key is not valid');
            }

            // Create a signature
            $signature = '';
            $success = openssl_sign($data, $signature, $privateKeyId, OPENSSL_ALGO_SHA256);

            // Free the private key resource
            openssl_free_key($privateKeyId);

            if (!$success) {
                Log::error('Failed to sign data');
                throw new Exception('Failed to sign data');
            }

            // Log::error('Signature: '. $signature);

            // Encode the signature to base64
            $encodedSignature = base64_encode($signature);
            
            // Log::info('Encoded signature: '. $encodedSignature);

            // Log::info('The end for generating signature');

            return $encodedSignature;
        } catch (Exception $e) {
            // Log the exception message
            Log::error('Signature generation failed: ' . $e->getMessage());

            // Rethrow the exception to be handled by the calling code
            throw $e;
        }
    }

    /**
     * Verify the given signature with the data using the public key.
     *
     * @param string $data
     * @param string $signature
     * 
     * @return bool
     */
    private function verify_signature($data, $signature)
    {
        try {
            $envObject = new ENVObject();
            
            // Load the public key from the file
            $publicKey = file_get_contents(storage_path('keys/vinnet/' . $envObject->environment . '/vinnet_public_key.pem'));

            // Check if the public key was successfully loaded
            if ($publicKey === false) {
                throw new Exception('Failed to load public key');
            }

            // Get a public key resource
            $pubKeyId = openssl_get_publickey($publicKey);

            // Check if the key resource is valid
            if (!$pubKeyId) {
                throw new Exception('Public key is not valid');
            }

            // Decode the hex signature to binary
            $decoded_signature = base64_decode($signature);

            // Check if the signature was successfully decoded
            if ($decoded_signature === false) {
                throw new Exception('Failed to decode signature');
            }
            
            // Log the data and signature for debugging purposes
            Log::info('Data to verify: ' . $data);
            Log::info('Signature to verify (base64): ' . $decoded_signature);
            
            // Verify the signature using SHA256 with RSA
            $verify = openssl_verify($data, $decoded_signature, $pubKeyId, OPENSSL_ALGO_SHA256);

            // Free the key resource
            openssl_free_key($pubKeyId);

            // Check if the verification process encountered an error
            if ($verify !== 1) {
                throw new Exception('An error occurred during signature verification: ' . openssl_error_string());
            }
            
            // Return the verification result
            return $verify === 1;
        } catch(Exception $e) {
            // Log the exception message
            Log::error('Signature verification failed: ' . $e->getMessage());

            // Rethrow the exception to be handled by the calling code
            throw $e;
        }
    }

    private function post_vinnet_request($url, $token, $postData)
    {
        try {
            // Log::info('Generate a POST request');

            // Initialize cURL session
            $ch = curl_init($url);

            // Convert post data to JSON format
            $jsonData = json_encode($postData);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $header = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ];

            if (!is_null($token)) {
                $header[] = 'Authorization: ' . $token;
            }

            Log::info('Headers: ' . implode(',', $header));
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            // Execute cURL session and get the response
            $response = curl_exec($ch);

            // Check if any error occurred
            if (curl_errno($ch)) {
                Log::error('Request Error: ' . curl_error($ch));
                throw new Exception(ProjectVinnetTransaction::ERROR_CODE_CONNECTION_FAILED);
            }
            
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL session
            curl_close($ch);

            // Handle response
            if ($httpCode == 200) {
                if($this->json_validator($response)){
                    $responseData = json_decode($response, true);
                } else {
                    $responseData = $response;
                }
                
                $verify_signature = $this->verify_signature($responseData['reqUuid'] . $responseData['resCode'] . $responseData['resMesg'] . $responseData['resData'], $responseData['sign']);

                if($verify_signature){
                    Log::info('Response data: ' . json_encode($responseData));
                    return json_encode($responseData);
                } else {
                    Log::info('Request failed with invalid signature');
                    throw new Exception('Request failed with invalid signature');
                }
            } else {
                throw new Exception('Request failed with HTTP code ' . $httpCode);
            }
        } catch (Exception $e) {
            // Log the exception message
            //Log::error('POST request failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
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

    /**
     * 
     * Perform multiple transactions simultaneously
     * 
     * @param request
     * 
     * @return string
     * 
     */
    public function perform_multiple_transactions(StoreProjectVinnetTokenRequest $request)
    {
        $step_info = "";

        try
        {
            $validatedRequest = $request->validated();

            // Log::info('URL: ' . $validatedRequest['url']);
            // Log::info('URL Decoded: ' . base64_decode($validatedRequest['url']));

            $decodedURL = base64_decode($validatedRequest['url']);

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

                if($interviewURL->channel != 'vinnet'){
                    throw new \Exception(ProjectVinnetTransaction::STATUS_TRANSACTION_FAILED);
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
                ($project->projectDetails->status === Project::STATUS_ON_GOING && strtolower($interviewURL->location_id) === '_defaultsp')){
                    
                    Log::info('Staging Environment: ');
                    
                    return response()->json([
                        'message' => ProjectVinnetTransaction::STATUS_TRANSACTION_TEST . ' [Giá trị quà tặng: ' . $price . ']'
                    ], Response::HTTP_OK);
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
                //Kiểm tra đáp viên đã thực hiện giao dịch nhận quà trước đó hay chưa?
                ProjectRespondent::checkIfRespondentProcessed($project, $interviewURL);

                //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó hay chưa?
                ProjectRespondent::checkGiftPhoneNumber($project, $validatedRequest['phone_number']);

            } catch(\Exception $e){
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage()
                ], 409);
            }

            DB::beginTransaction();

            try{
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
                    'service_type' => $validatedRequest['service_type'],
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
            
            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);

            Log::info('Project respondent: ' . json_encode($projectRespondent->toArray()));
            
            Log::info('Authentication Token to make API calls and retrieve information about suitable carrier pricing tiers.');
            
            $step_info = "Authentication Token API";

            $tokenData = $this->authenticate_token();

            if($tokenData['code'] != 0)
            {
                Log::error('Authenticate Token: ' . ProjectVinnetTransaction::STATUS_NOT_RECEIVED . ' => ' . $tokenData['message']);

                $projectRespondent->updateStatus(ProjectVinnetTransaction::STATUS_NOT_RECEIVED);
                
                return response()->json([
                    'message' => ProjectVinnetTransaction::STATUS_NOT_RECEIVED,
                    'error' => 'Authenticate Token: ' . ProjectVinnetTransaction::STATUS_NOT_RECEIVED . ' => ' . $tokenData['message']
                ], 404);
            }

            $step_info = "Query Service API";

            $serviceItemsData = $this->query_service($validatedRequest['phone_number'], $validatedRequest['service_code'], $tokenData['token'], null);
            
            Log::info('Code: ' . $serviceItemsData['code']);

            if($serviceItemsData['code'] != 0)
            {
                Log::error('Query Services: ' . ProjectVinnetTransaction::STATUS_ERROR . ' => ' . $serviceItemsData['message']);

                // Log the exception message
                $projectRespondent->updateStatus(ProjectVinnetTransaction::STATUS_ERROR);

                return response()->json([
                    'message' => ProjectVinnetTransaction::STATUS_ERROR,
                    'error' => 'Query Services: ' . ProjectVinnetTransaction::STATUS_ERROR . ' => ' . $serviceItemsData['message']
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
                    'message' => ProjectVinnetTransaction::STATUS_INVALID_DENOMINATION,
                    'error' => 'Không tìm được mệnh giá phù hợp.'
                ], 404);
            }
            
            Log::info('Selected Prices: ' . implode(", ", $selectedPrices));
            
            $vinnet_token_order = 1;

            foreach($selectedPrices as $selectedPrice)
            {
                Log::info('Transaction ' . $vinnet_token_order . ': ' . $selectedPrice);

                $vinnetTransaction = $projectRespondent->createVinnetTransactions([
                    'project_respondent_id' => $projectRespondent->id,
                    'vinnet_serviceitems_requuid' => $serviceItemsData['reqUuid'],
                    'vinnet_token_requuid' => $tokenData['reqUuid'],
                    'vinnet_token' => $tokenData['token'],
                    'vinnet_token_order' => $vinnet_token_order,
                    'vinnet_token_status' => ProjectVinnetTransaction::STATUS_PENDING_VERIFICATION
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

                $payItemData = $this->pay_service($validatedRequest['phone_number'], $validatedRequest['service_code'], $tokenData['token'], $selectedServiceItem);

                if($payItemData['code'] == 0)
                {
                    $statusPaymentServiceResult = $vinnetTransaction->updatePaymentServiceStatus($payItemData['reqUuid'], $payItemData['pay_item'], ProjectVinnetTransaction::STATUS_VERIFIED, $payItemData['message']);
                    
                    $projectRespondent->updateStatus(ProjectRespondent::STATUS_RESPONDENT_GIFT_RECEIVED);

                    if(!empty($payItemData['pay_item']['cardItems']) && is_array($payItemData['pay_item']['cardItems'])){
                        
                        $smsTransaction = $vinnetTransaction->createVinnetSMSTransaction([
                            'vinnet_transaction_id' => $vinnetTransaction->id,
                            'sms_status' => ProjectVinnetSMSTransaction::STATUS_SMS_PENDING
                        ]);
                        
                        $card_item = $payItemData['pay_item']['cardItems'][0];

                        $this->card_serial_no = $card_item['serialNo'] ?? null;
                        $this->card_pin_code = $card_item['pinCode'] ?? null;
                        $this->card_expiry_date = $card_item['expiryDate'] ?? null;

                        $messageCard = sprintf(
                            "Code: %s | Seri: %s | HSD: %s",
                            $card_item['pinCode'] ?? 'N/A',
                            $card_item['serialNo'] ?? 'N/A',
                            $card_item['expiryDate'] ?? 'N/A'
                        );
                        
                        $apiCMCObject = new APICMCObject();

                        $responseSMSData = $apiCMCObject->send_sms($validatedRequest['phone_number'], $messageCard);

                        switch(intval($responseSMSData['status']))
                        {
                            case 1:
                                $smsTransactionStatus = $smsTransaction->updateStatus(ProjectVinnetSMSTransaction::STATUS_SMS_SUCCESS);
                                break;
                            default:
                                $smsTransactionStatus = $smsTransaction->updateStatus($responseSMSData['statusDescription']);
                                break;
                        }
                    }
                }
                else 
                {
                    if ($payItemData['code'] == 99)
                    {
                        $statusPaymentServiceResult = $vinnetTransaction->updatePaymentServiceStatus($payItemData['reqUuid'], null, ProjectVinnetTransaction::STATUS_UNDETERMINED_TRANSACTION_RESULT, $payItemData['message']);
                        $projectRespondent->updateStatus(ProjectVinnetTransaction::STATUS_ERROR);

                        return response()->json([
                            'message' => ProjectVinnetTransaction::STATUS_ERROR,
                            'error' => 'Giao dịch bị quá tải, PVV vui lòng chờ 3-4 phút, sau đó kiểm tra đáp viên xem đã nhận được quà tặng chưa. Nếu chưa, vui lòng liên hệ Admin để kiểm tra.'
                        ], 404);
                    } 
                    else 
                    {
                        Log::error(ProjectVinnetTransaction::STATUS_ERROR . ' [' . $payItemData['message'] . ']');

                        $statusPaymentServiceResult = $vinnetTransaction->updatePaymentServiceStatus($payItemData['reqUuid'], null, ProjectVinnetTransaction::STATUS_ERROR, $payItemData['message']);
                        $projectRespondent->updateStatus(ProjectVinnetTransaction::STATUS_ERROR);

                        return response()->json([
                            'message' => ProjectVinnetTransaction::STATUS_ERROR . ' [' . $payItemData['message'] . ']',
                            'error' => $payItemData['message']
                        ], 404);
                    }
                }

                $vinnet_token_order++;
            }

            return response()->json([
                'message' => ProjectVinnetTransaction::SUCCESS_TRANSACTION_CONTACT_ADMIN_IF_NO_GIFT
            ], 200);

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
    public function change_key(Request $request)
    {
        try{
            Log::info('Changing key');

            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            $uuid = $this->generate_formated_uuid();
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
     * 
     * Authenticate Token: Lấy access token sử dụng cho các bản tin nghiệp vụ.
     * 
     * #return string
     * 
     */
    public function authenticate_token()
    {
        try{
            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            $uuid = $this->generate_formated_uuid();
            Log::info('UUID to authenticate token: ' . $uuid);

            $reqData = $this->encrypt_data(json_encode(['merchantKey' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_KEY'])]));

            //Log::info('Encrypted data: ' . $reqData);

            Log::info('Signature: ' . str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $signature = $this->generate_signature(str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '', $url) . '/authen', null, $postData);

            $decodedResponse = json_decode($response, true);

            if($decodedResponse === null && json_last_error() != JSON_ERROR_NONE){
                Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }

            if($decodedResponse['resCode'] == '00'){

                $decryptedData = $this->decrypt_data($decodedResponse['resData']);

                $decodedData = json_decode($decryptedData, true);

                if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error: ' . json_last_error_msg());
                    throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
                }
                
                $decodedData = json_decode($decodedData, true);

                if (!is_array($decodedData)) {
                    Log::error('Decoded services data is not an array');
                    throw new \Exception('Decoded services data is not an array');
                }

                Log::info('Token: ' . $decodedData['token']);
                
                return [
                    'reqUuid' => $uuid,
                    'token' => $decodedData['token'],
                    'code' => (int)$decodedResponse['resCode'],
                    'message' => $decodedResponse['resMesg']
                ]; 
            } else {
                throw new \Exception($decodedResponse['resMesg']);
            }
        } catch (Exception $e){
            //Log the exception message
            //Log::error('Token authentication failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
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
    public function merchantinfo(Request $request)
    {
        try{
            Log::info("Merchant Information");
            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            $tokenData = $this->authenticate_token();

            $uuid = $this->generate_formated_uuid();

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

    /**
     * 
     * Query service: Truy vấn dịch vụ từ hệ thống Vinnet.
     * 
     * @param phone_number
     * @param service_code
     * @param token
     * @param price
     * 
     * @return json
     * 
     */
    private function query_service($phone_number, $service_code, $token, $price)
    {
        try {
            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;

            // Validate inputs
            if (empty($phone_number) || empty($service_code)|| empty($token)) {
                throw new \InvalidArgumentException('One or more required (phone_number|service_code|token) fields are empty');
            }

            $uuid = $this->generate_formated_uuid();
            //Log::info('UUID: ' . $uuid);
            
            $dataRequest = [
                'serviceCode' => $service_code,
                'recipient' => $phone_number
            ];

            //Log::info('Data request: ' . json_encode($dataRequest));
            
            $reqData = $this->encrypt_data(json_encode($dataRequest));

            //Log::info('Encrypted data: ' . $reqData);

            //Log::info('Data signature: ' . env('VINNET_MERCHANT_CODE') . $uuid . $reqData);

            $signature = $this->generate_signature(str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '',$url) . '/queryservice', $token, $postData);

            //Log::info('Service: ' . $response);

            $decodedResponse = json_decode($response, true);

            if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
            }

            if($decodedResponse['resCode'] == '00')
            {
                $resDatas = str_split($decodedResponse['resData'], 344);

                $strServices = '';

                foreach($resDatas as $resData){
                    Log::info($resData);
                    $decryptedData = $this->decrypt_data($resData);
                    $strServices = $strServices . $decryptedData;
                    Log::info($decryptedData);
                }

                //Log::info($strServices);
            
                $strServices = str_replace("\"\"", "", $strServices);

                //Log::info($strServices);

                //Decode JSON string to PHP array
                $decodedServicesData = json_decode(json_decode($strServices, true), true);

                if ($decodedServicesData === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error: ' . json_last_error_msg());
                    throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
                }

                //Log::info("Step 2: " . print_r($decodedServicesData, true));
            
                if (!is_array($decodedServicesData)) {
                    throw new \Exception('Decoded services data is not an array');
                }
                
                if (!isset($decodedServicesData['serviceItems']) || !is_array($decodedServicesData['serviceItems'])) {
                    throw new \Exception('serviceItems key is missing or not an array');
                }

                //Log::info("Service items: " . json_encode($decodedServicesData));

                // Access serviceItems directly as an array
                $serviceItems = $decodedServicesData['serviceItems'];
                
                //Log::info("Service items: " . json_encode($serviceItems));

                // Filter serviceItems with itemValue = $price using Laravel Collection
                
                if($price){
                    $selectedServiceItems = collect($serviceItems)->filter(function ($item) use ($price) {
                        return $item['itemValue'] === $price;
                    })->values()->first();

                    Log::info("Service Items: " . print_r($selectedServiceItems, true) . " - Check: " . is_array($selectedServiceItems));

                    return [
                        'reqUuid' => $uuid,
                        'service_items' => array($selectedServiceItems),
                        'code' => (int)$decodedResponse['resCode'],
                        'message' => $decodedResponse['resMesg']
                    ];
                } else {
                    Log::info("Service Items: " . print_r($serviceItems, true) . is_array($serviceItems));

                    return [
                        'reqUuid' => $uuid,
                        'service_items' => $serviceItems,
                        'code' => (int)$decodedResponse['resCode'],
                        'message' => $decodedResponse['resMesg']
                    ];
                }
            } else {
                //throw new \Exception($decodedResponse['resMesg']);
                return [
                    'reqUuid' => $uuid,
                    'code' => (int)$decodedResponse['resCode'],
                    'message' => $decodedResponse['resMesg']
                ];
            }
        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Query service failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
        }
    }

    /**
     * 
     * Pay service: Thanh toán dịch vụ từ hệ thống Vinnet.
     * 
     * @param phone_number
     * @param service_code
     * @param token
     * @param service_item
     * 
     * @return json
     *  
     */
    private function pay_service($phone_number, $service_code, $token, $service_item)
    {
        try {
            $envObject = new ENVObject();
            $environment = $envObject->environment;
            $merchantInfo = $envObject->merchantInfo;
            $url = $envObject->url;
            
            //$encodedServiceItem = json_decode($service_item, true);
            
            $dataRequest = [
                'serviceCode' => $service_code, 
                'recipient' => $phone_number,
                'recipientType' => 'TT',
                'serviceItem' => $service_item,
                'quantity' => 1
            ];

            Log::info($dataRequest);

            $uuid = $this->generate_formated_uuid();
            Log::info('UUID: ' . $uuid);

            Log::info('Data request: ' . json_encode($dataRequest));
            
            $reqData = $this->encrypt_data(json_encode($dataRequest));

            Log::info('Encrypted data: ' . $reqData);

            Log::info('Data signature: ' . str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $signature = $this->generate_signature(str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $merchantInfo['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '', $url) . '/payservice', $token, $postData);

            Log::info('Pay service response: ' . $response);

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
                Log::info('Pay service data: ' . $decodedResponse['resData']);

                $decryptedData = $this->decrypt_data($decodedResponse['resData']);

                Log::info('Decrypted pay service data: ' . $decryptedData);
                
                $decodedData = json_decode($decryptedData, true);

                if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON Decode Error: ' . json_last_error_msg());
                    throw new \Exception('JSON Decode Error: ' . json_last_error_msg());
                }

                $decodedData = json_decode($decodedData, true);

                if (!is_array($decodedData)) {
                    Log::error('Decoded services data is not an array');
                    throw new \Exception('Decoded services data is not an array');
                }

                Log::info('Pay item array: ' . print_r($decodedData, true));

                return [
                    'reqUuid' => $uuid,
                    'pay_item' => $decodedData,
                    'code' => (int)$decodedResponse['resCode'],
                    'message' => $decodedResponse['resMesg']
                ];
            } else {
                //throw new \Exception($decodedResponse['resMesg']);
                return [
                    'reqUuid' => $uuid,
                    'code' => (int)$decodedResponse['resCode'],
                    'message' => $decodedResponse['resMesg']
                ];
            }

        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Pay service failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
        }
    }
}
