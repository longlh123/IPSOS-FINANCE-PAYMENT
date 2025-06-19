<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;
use App\Models\Employee;
use App\Models\ProjectVinnetToken;
use App\Models\InterviewURL;
use App\Models\VinnetUUID;
use App\Models\ENVObject;
use App\Http\Requests\StoreProjectVinnetTokenRequest;
use App\Http\Resources\VinnetProjectResource;

class VinnetController extends Controller
{
    public function transactions_view()
    {
        try
        {
            $query = ProjectVinnetToken::with([ 'project','employee','employee.role','employee.team','province']);

            $vinnetData = $query->get();

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => VinnetProjectResource::collection($vinnetData) 
            ]);
        }
        catch(\Exception $e)
        {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ]);
        }
    }

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

            $merchant_info = [
                'VINNET_MERCHANT_CODE' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'VINNET_MERCHANT_KEY' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_KEY']),
            ];

            return response()->json([
                'status_code' => Response::HTTP_OK, //200
                'message' => 'Successful request.',
                'data' => $merchant_info
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

            // Load the public key from the file
            $publicKeyPath = storage_path('keys/vinnet_public_key.pem');
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

            $privateKey = file_get_contents(storage_path('keys/private_key.pem'));

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

            // Load the public key from the file
            $privateKeyPath = storage_path('keys/private_key.pem');
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
            // Load the public key from the file
            $publicKey = file_get_contents(storage_path('keys/vinnet_public_key.pem'));

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
                throw new Exception(ProjectVinnetToken::ERROR_CODE_CONNECTION_FAILED);
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

    protected function setEnvValue($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents(
                $path,
                preg_replace(
                    '/^' . $key . '=.*/m',
                    $key . '=' . $value,
                    file_get_contents($path)
                )
            );
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
    
    public function check_transaction($interviewURL)
    {
        try
        {
            Log::info('Step 1: Check the project information:');

            $projectQuery = Project::with('projectDetails', 'projectVinnetTokens')
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
                if($project->projectDetails->status !== Project::STATUS_ON_GOING){
                    Log::error(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                    throw new \Exception(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                } 
            }

            Log::info('Step 2: Check the respondent information:');

            $respondentProcessed = $project->projectVinnetTokens()->where('respondent_id', $interviewURL->respondent_id)->exists();
            
            if($respondentProcessed){
                Log::error(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
                throw new \Exception(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
            }

            Log::info('Step 3: Check respondent phone number information:');

            $respPhoneNumberProcessed = $project->projectVinnetTokens()
                ->where(function($query) use ($interviewURL) {
                    $query->where('respondent_phone_number', $interviewURL->respondent_phone_number)
                        ->orWhere('phone_number', $interviewURL->respondent_phone_number);
                })->exists();
            
            if($respPhoneNumberProcessed){
                Log::error(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
                throw new \Exception(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
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
                $projectVinnetToken = ProjectVinnetToken::create([
                    'project_id' => $project->id,
                    'shell_chainid' => $interviewURL->shell_chainid,
                    'respondent_id' => $interviewURL->respondent_id,
                    'employee_id' => $interviewURL->employee->id,
                    'province_id' => $interviewURL->province_id,
                    'interview_start' => $interviewURL->interview_start,
                    'interview_end' => $interviewURL->interview_end,
                    'respondent_phone_number' => $interviewURL->respondent_phone_number,
                    'phone_number' => $interviewURL->respondent_phone_number,
                    'vinnet_token_requuid' => 'None',
                    'vinnet_token' => 'None',
                    'vinnet_token_order' => 1,
                    'vinnet_token_status' => Project::STATUS_REJECT_REASON_PHONE_NUMBER,
                    'reject_message' => $reject_message,
                ]);

                // Check if the record was successfully created
                if (!$projectVinnetToken) {
                    throw new \Exception('Failed to create the ProjectVinnetToken record');
                }

                Log::info('Token stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);

                return response()->json([
                    'status_code' => Response::HTTP_OK, //200
                    'message' => ProjectVinnetToken::ERROR_TRANSACTION_DENIED_FEEDBACK_RECORDED
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
        try{
            $validatedRequest = $request->validated();

            // Log::info('URL: ' . $validatedRequest['url']);
            // Log::info('URL Decoded: ' . base64_decode($validatedRequest['url']));

            $decodedURL = base64_decode($validatedRequest['url']);

            $splittedURL = explode("/", $decodedURL);

            Log::info('URL Splitted: ' . json_encode($splittedURL));
            
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

                if(!$project){
                    Log::error(Project::STATUS_PROJECT_NOT_FOUND);
                    throw new \Exception(Project::STATUS_PROJECT_NOT_FOUND);
                } else {
                    if($project->projectDetails->status !== Project::STATUS_ON_GOING){
                        Log::error(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                        throw new \Exception(Project::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                    } 
                }

                Log::info('Step 4: Check phone number using send a present:');

                $phoneNumberProcessed = $project->projectVinnetTokens()
                    ->where(function($query) use ($validatedRequest) {
                        $query->where('respondent_phone_number', $validatedRequest['phone_number'])
                            ->orWhere('phone_number', $validatedRequest['phone_number']);
                    })->exists();
                
                if($phoneNumberProcessed){
                    Log::error(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
                    throw new \Exception(ProjectVinnetToken::STATUS_TRANSACTION_ALREADY_EXISTS);
                }
                
                Log::info('Step 5: Find price item');
                
                $price_item = $project->projectProvinces->firstWhere('province_id', $interviewURL->province_id);

                if(!$price_item){
                    Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
                    throw new \Exception(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
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
                        $price = intval($price_item->price_boosters);
                        break;
                    case 'boosters_1':
                        $price = intval($price_item->price_boosters_1);
                        break;
                    case 'boosters_2':
                        $price = intval($price_item->price_boosters_2);
                        break;
                    case 'boosters_3':
                        $price = intval($price_item->price_boosters_3);
                        break;
                    case 'boosters_4':
                        $price = intval($price_item->price_boosters_4);
                        break;
                    case 'boosters_5':
                        $price = intval($price_item->price_boosters_5);
                        break;
                }
                
                Log::info('Price: ' . $price);

                Log::info('Step 6: Authentication Token to make API calls and retrieve information about suitable carrier pricing tiers.');

                $tokenData = $this->authenticate_token();

                $serviceItemsData = $this->query_service($validatedRequest['phone_number'], $validatedRequest['service_code'], $tokenData['token'], null);

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
                
                Log::info('Selected Prices: ' . implode(", ", $selectedPrices));
                
                $vinnet_token_order = 1;

                foreach($selectedPrices as $selectedPrice)
                {
                    Log::info('Step 8 - Transaction ' . $vinnet_token_order . ': ' . $selectedPrice);

                    $this->perform_transaction($interviewURL, $validatedRequest['phone_number'], $validatedRequest['service_code'], $vinnet_token_order, $selectedPrice);
                    $vinnet_token_order++;
                }

                return response()->json([
                    'status_code' => Response::HTTP_OK, //200
                    'message' => ProjectVinnetToken::SUCCESS_TRANSACTION_CONTACT_ADMIN_IF_NO_GIFT
                ], Response::HTTP_OK);
            }
            else 
            {
                return response()->json([
                    'status_code' => Response::HTTP_BAD_REQUEST, //200
                    'message' => 'Unsuccessful request.'
                ], Response::HTTP_BAD_REQUEST);
            }

        } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST, //400
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function perform_transaction($interviewURL, $phone_number, $service_code, $vinnet_token_order, $price)
    {
        try{
            Log::info('Step 8.1 - ' . $vinnet_token_order . ': Authentication Token:');

            $tokenData = $this->authenticate_token();

            Log::info('Step 8.2 - ' . $vinnet_token_order . ': Store the token:');

            $this->store_vinnet_token($interviewURL, $phone_number, $tokenData['reqUuid'], $tokenData['token'], $tokenData['message'], $vinnet_token_order);
            
            Log::info('Step 8.3 - ' . $vinnet_token_order . ': Request Service Item:');

            $serviceItemsData = $this->query_service($phone_number, $service_code, $tokenData['token'], $price);

            if($serviceItemsData['code'] == 0)
            {
                Log::info('Step 8.4 - ' . $vinnet_token_order . ': Update the status of token: ' . ProjectVinnetToken::STATUS_PENDING_VERIFICATION);

                $updated_result = $this->update_status_vinnet_token($interviewURL, $phone_number, $serviceItemsData['reqUuid'], null, $tokenData['token'], ProjectVinnetToken::STATUS_PENDING_VERIFICATION, $serviceItemsData['message'], null);

                Log::info('Step 8.5 - ' . $vinnet_token_order . ': Request Pay Service');

                $payItemData = $this->pay_service($phone_number, $service_code, $tokenData['token'], $serviceItemsData['service_items'][0]);
                
                if($payItemData['code'] == 0)
                {
                    Log::info('Step 8.6 - ' . $vinnet_token_order . ': Update the status of token: ' . ProjectVinnetToken::STATUS_VERIFIED);

                    $updated_result = $this->update_status_vinnet_token($interviewURL, $phone_number, null, $payItemData['reqUuid'] , $tokenData['token'], ProjectVinnetToken::STATUS_VERIFIED, $payItemData['message'], $payItemData['pay_item']);
                }
                else if ($payItemData['code'] == 99)
                {
                    $this->update_status_vinnet_token($interviewURL, $phone_number, null, $payItemData['reqUuid'], $tokenData['token'], ProjectVinnetToken::STATUS_UNDETERMINED_TRANSACTION_RESULT, $payItemData['message'], null);

                    throw new \Exception('Giao bị quá tải, PVV vui lòng chờ 3-4 phút, sau đó kiểm tra đáp viên xem đã nhận được quà tặng chưa. Nếu chưa, vui lòng liên hệ Admin để kiểm tra.');
                } 
                else 
                {
                    $this->update_status_vinnet_token($interviewURL, $phone_number, null, $payItemData['reqUuid'], $tokenData['token'], ProjectVinnetToken::STATUS_ERROR, $payItemData['message'], null);

                    throw new \Exception($payItemData['message']);
                }
            } 
            else 
            {
                // Log the exception message
                $this->update_status_vinnet_token($interviewURL, $phone_number, $serviceItemsData['reqUuid'], null, $tokenData['token'], ProjectVinnetToken::STATUS_ERROR, $serviceItemsData['message'], null);

                throw new \Exception($serviceItemsData['message']);
            }

            return true;
        } catch(Exception $e) {
            
            Log::error($e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
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
            $envObject = new ENVObject();

            $uuid = $this->generate_formated_uuid();
            Log::info('UUID: ' . $uuid);

            $reqData = $this->encrypt_data(json_encode(['oldMerchantKey' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_KEY'])]));
            
            $signature = $this->generate_signature(str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            Log::info('Data post: ' . json_encode($postData));
            
            $response = $this->post_vinnet_request(str_replace('"', '', $envObject->env['VINNET_URL']) . '/changekey', null, $postData);

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
            //$this->setEnvValue('VINNET_MERCHANT_KEY', $decodedData['newMerchantKey']);
            $envObject->updateEnv([
                'VINNET_MERCHANT_KEY' =>  $decodedData['newMerchantKey']
            ]);

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

            $uuid = $this->generate_formated_uuid();
            Log::info('UUID to authenticate token: ' . $uuid);

            $reqData = $this->encrypt_data(json_encode(['merchantKey' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_KEY'])]));

            //Log::info('Encrypted data: ' . $reqData);

            Log::info('Signature: ' . str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $signature = $this->generate_signature(str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '', $envObject->env['VINNET_URL']) . '/authen', null, $postData);

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

                //Log::info('Token: ' . $decodedData['token']);
                
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

    public function merchantinfo(Request $request)
    {
        try{
            Log::info("Merchant Information");
            $envObject = new ENVObject();

            $tokenData = $this->authenticate_token();

            $uuid = $this->generate_formated_uuid();

            $dataRequest = [];

            $reqData = $this->encrypt_data(json_encode($dataRequest));

            $signature = $this->generate_signature(str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            Log::info('Merchant Information [$postData]: ' . json_encode($postData));

            $response = $this->post_vinnet_request(str_replace('"', '', $envObject->env['VINNET_URL']) . '/merchantinfo', $tokenData['token'], $postData);

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
     * Store the token 
     * 
     * @param internal_code
     * @param project_name
     * @param respondent_id
     * @param phone_number
     * @param token
     * 
     * @return boolean
     * 
     */
    public function store_vinnet_token($interviewURL, $phone_number, $reqUuid, $token, $vinnet_token_status, $vinnet_token_order)
    {
        try
        {
            $projectQuery = Project::with('projectDetails', 'projectVinnetTokens')
                ->where('internal_code', $interviewURL->internal_code)
                ->where('project_name', $interviewURL->project_name)
                ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                    $query->where('remember_token', $interviewURL->remember_token);
                });
            
            $project = $projectQuery->first();
            
            //Create the respondent with validated data
            $projectVinnetToken = ProjectVinnetToken::create([
                'project_id' => $project->id,
                'shell_chainid' => $interviewURL->shell_chainid,
                'respondent_id' => $interviewURL->respondent_id,
                'employee_id' => $interviewURL->employee->id,
                'province_id' => $interviewURL->province_id,
                'interview_start' => $interviewURL->interview_start,
                'interview_end' => $interviewURL->interview_end,
                'respondent_phone_number' => $interviewURL->respondent_phone_number,
                'phone_number' => $phone_number,
                'vinnet_token_requuid' => $reqUuid,
                'vinnet_token' => $token,
                'vinnet_token_order' => $vinnet_token_order,
                'vinnet_token_status' => $vinnet_token_status,
                'status' => ProjectVinnetToken::STATUS_ISSUED
            ]);

            // Check if the record was successfully created
            if (!$projectVinnetToken) {
                throw new \Exception('Failed to create the ProjectVinnetToken record');
            }

            Log::info('Token stored successfully', ['internal_code' => $interviewURL->internal_code, 'respondent_id' => $interviewURL->respondent_id]);

            return true;
        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Token storage failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
        }
    }

    /**
     * 
     * Update the status of token
     * 
     * @param internal_code
     * @param project_name
     * @param respondent_id
     * @param phone_number
     * @param token
     * @param new_status
     * @param pay_item
     * 
     * @return boolean
     * 
     */
    public function update_status_vinnet_token($interviewURL, $phone_number, $service_items_reqUuid, $pay_service_reqUuid, $token, $new_status, $vinnet_token_status, $pay_item)
    {
        try{
            Log::info('Updte status vinnet token');

            //Find the record and update the status
            $query = ProjectVinnetToken::query();
            $query->where('respondent_id', $interviewURL->respondent_id);
            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number);
            $query->where('phone_number', $phone_number);
            $query->where('vinnet_token', $token);
            $query->whereHas('project', function(Builder $query) use ($interviewURL){
                $query->where('internal_code', $interviewURL->internal_code);
                $query->where('project_name', $interviewURL->project_name);
            });

            $record = $query->first();

            if(!$record){
                Log::warning("No record found to update for respondent_id:" . $interviewURL->respondent_id);
                throw new \Exception('No record found to update');
            }

            Log::info('Updte status vinnet token [$record]: '.json_encode($record));

            //Update the status
            $record->status = $new_status;
            $record->vinnet_token_status = $vinnet_token_status;

            if($pay_item){
                $record->total_amt = $pay_item['totalAmt'];
                $record->commission = $pay_item['commission'];
                $record->discount = $pay_item['discount'];
                $record->payment_amt = $pay_item['paymentAmt'];
            }

            if($service_items_reqUuid){
                $record->vinnet_serviceitems_requuid = $service_items_reqUuid;
            }
            
            if($pay_service_reqUuid){
                $record->vinnet_payservice_requuid = $pay_service_reqUuid;
            }

            $record->save();

            // Return a success response
            return true;
        } catch(\Exception $e){
            // Log the exception message
            Log::error('The status of token updating failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
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

            $signature = $this->generate_signature(str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '', $envObject->env['VINNET_URL']) . '/queryservice', $token, $postData);

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

            Log::info('Data signature: ' . str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $signature = $this->generate_signature(str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']) . $uuid . $reqData);

            $postData = [
                'merchantCode' => str_replace('"', '', $envObject->env['VINNET_MERCHANT_CODE']),
                'reqUuid' => $uuid,
                'reqData' => $reqData,
                'sign' => $signature
            ];

            $response = $this->post_vinnet_request(str_replace('"', '', $envObject->env['VINNET_URL']) . '/payservice', $token, $postData);

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
