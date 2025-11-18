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
use App\Constants\SMSStatus;
use App\Constants\TransactionStatus;

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
            $maxRetries = 2; // Số lần thử lại nếu request thất bại
            $retryDelay = 2; // Giây chờ giữa các lần thử
            $attempt = 0;

            do {
                $attempt++;
                $startTime = microtime(true); // Bắt đầu đếm thời gian

                try {
                    // Initialize cURL session
                    $ch = curl_init($url);

                    // Convert post data to JSON format
                    $jsonData = json_encode($postData);

                    // Prepare headers
                    $header = [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($jsonData)
                    ];

                    if (!is_null($token)) {
                        $header[] = 'Authorization: ' . $token;
                    }

                    Log::info("POST attempt {$attempt} to URL: {$url}");
                    Log::info('Headers: ' . implode(',', $header));
                    Log::info('Payload: ' . $jsonData);

                    // Set cURL options
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER     => $header,
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => $jsonData,
                        CURLOPT_CONNECTTIMEOUT => 10, // Giây tối đa chờ kết nối
                        CURLOPT_TIMEOUT        => 20, // Giây tối đa cho toàn request
                    ]);

                    // Execute request
                    $response = curl_exec($ch);
                    $duration = round(microtime(true) - $startTime, 2); // Tính thời gian thực thi

                    if (curl_errno($ch)) {
                        $curlError = curl_error($ch);
                        Log::error("cURL error (attempt {$attempt}, {$duration}s): {$curlError}");
                        curl_close($ch);

                        // Retry nếu là lỗi timeout hoặc lỗi kết nối
                        if (str_contains($curlError, 'timed out') || str_contains($curlError, 'Failed to connect')) {
                            if ($attempt < $maxRetries) {
                                Log::warning("Retrying in {$retryDelay}s...");
                                sleep($retryDelay);
                                continue;
                            }
                        }
                        throw new Exception("Kết nối tới máy chủ Vinnet thất bại: {$curlError}");
                    }

                    // Lấy mã HTTP
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    Log::info("Response received in {$duration}s (HTTP {$httpCode})");

                    // Kiểm tra response rỗng
                    if ($response === false || $response === null || trim($response) === '') {
                        throw new Exception("Máy chủ Vinnet không phản hồi hoặc trả về dữ liệu rỗng");
                    }

                    // HTTP code khác 200
                    if ($httpCode !== 200) {
                        Log::error("Request failed with HTTP code {$httpCode}. Response: {$response}");
                        throw new Exception("Request thất bại với HTTP code {$httpCode}");
                    }

                    // Kiểm tra JSON
                    if ($this->json_validator($response)) {
                        $responseData = json_decode($response, true);
                    } else {
                        Log::warning("Response không hợp lệ JSON: {$response}");
                        throw new Exception("Response không đúng định dạng JSON");
                    }

                    // Kiểm tra trường cần thiết
                    if (!isset($responseData['reqUuid'], $responseData['resCode'], $responseData['resMesg'], $responseData['resData'], $responseData['sign'])) {
                        throw new Exception("Response thiếu các trường cần thiết để xác thực chữ ký");
                    }

                    // Xác thực chữ ký
                    $verify_signature = $this->verify_signature(
                        $responseData['reqUuid'] . $responseData['resCode'] . $responseData['resMesg'] . $responseData['resData'],
                        $responseData['sign']
                    );

                    if (!$verify_signature) {
                        Log::error('Request failed with invalid signature');
                        throw new Exception('Xác thực chữ ký không hợp lệ (invalid signature)');
                    }

                    Log::info('Response data: ' . json_encode($responseData));
                    return json_encode($responseData);
                } catch (Exception $innerEx) {
                    Log::error("POST attempt {$attempt} failed: " . $innerEx->getMessage());

                    // Nếu còn lượt retry → chờ rồi thử lại
                    if ($attempt < $maxRetries) {
                        Log::warning("Retrying in {$retryDelay}s...");
                        sleep($retryDelay);
                    } else {
                        throw $innerEx;
                    }
                }

            } while ($attempt < $maxRetries);

        } catch (Exception $e) {
            Log::error('POST request to Vinnet failed: ' . $e->getMessage());
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
            } else {
                //Thông tin cũ
                //Kiểm tra xem Project Respondent có thực hiện bất kỳ giao dịch nào chưa?
                //Nếu chưa => xem như thông tin mới => cập nhật lại status cho Project Respondent

                Log::info("Number of transactions: " . $projectRespondent->vinnetTransactions()->count());

                if($projectRespondent->vinnetTransactions()->count() == 0){

                    $projectRespondent->update([
                        'phone_number' => $validatedRequest['phone_number'],
                        'service_code' => $validatedRequest['service_code'],
                    ]);

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
            
            Log::info('Transaction stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->shell_chainid . '-' . $interviewURL->respondent_id]);

            Log::info('Project respondent: ' . json_encode($projectRespondent->toArray()));
            
            Log::info('Authentication Token to make API calls and retrieve information about suitable carrier pricing tiers.');
            
            $step_info = "Authentication Token API";

            try{
                $tokenData = $this->authenticate_token();

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
                $serviceItemsData = $this->query_service(
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

                $payServiceUuid = $this->generate_formated_uuid();
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
                    $payItemData = $this->pay_service(
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
                        "Cam on ban da tham gia. IPSOS tang ban:\n%s",
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
    private function pay_service($uuid, $phone_number, $service_code, $token, $service_item)
    {
        try 
        {
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
