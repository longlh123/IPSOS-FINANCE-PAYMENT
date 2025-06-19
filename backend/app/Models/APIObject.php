<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class APIObject
{
    private $url;
    private $header;
    
    public function __construct($url, $header){
        $this->url = $url;
        $this->header = $header;
    }

    public function get_request()
    {
        try {
            // Initialize cURL session
            $ch = curl_init($this->url);
            
            Log::info("URL: " . $this->url);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header); // Set headers if any

            // Execute cURL session and get the response
            $response = curl_exec($ch);

            // Check if any error occurred
            if (curl_errno($ch)) {
                Log::error('Request Error: ' . curl_error($ch));
                throw new \Exception('Request Error: ' . curl_error($ch));
            }
            
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL session
            curl_close($ch);

            // Handle response
            if ($httpCode == 200) {
                // Handle the response, e.g., parse JSON or plain text
                if($this->json_validator($response)){
                    $responseData = json_decode($response, true);
                } else {
                    $responseData = $response;
                }

                // Log::info('Response data: ' . $response);
                return $responseData;
            } else {
                throw new \Exception('Request failed with HTTP code ' . $httpCode);
            }
        } catch (Exception $e) {
            // Log the exception message
            Log::error('GET request failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
        }
    }

    public function post_request($postData)
    {
        try 
        {
            // Initialize cURL session
            $ch = curl_init($this->url);
            
            // Convert post data to JSON format
            if(empty($postData)){
                $jsonData = json_encode(array());
            } else {
                $jsonData = json_encode($postData);
            }
            
            Log::info("Post Data: ".$jsonData);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $header = $this->header;

            Log::info('Headers: ' . implode(',', $header));
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            // Execute cURL session and get the response
            $response = curl_exec($ch);

            // Check if any error occurred
            if (curl_errno($ch)) {
                Log::error('Request Error: ' . curl_error($ch));
                throw new \Exception('Request Error: ' . curl_error($ch));
            }
            
            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL session
            curl_close($ch);

            // Handle response
            if ($httpCode == 200) 
            {
                if($this->json_validator($response)){
                    $responseData = json_decode($response, true);
                } else {
                    $responseData = $response;
                }

                return $responseData;
                
                // $verify_signature = $this->verify_signature($responseData['reqUuid'] . $responseData['resCode'] . $responseData['resMesg'] . $responseData['resData'], $responseData['sign']);

                // if($verify_signature){
                //     Log::info('Response data: ' . json_encode($responseData));
                //     return json_encode($responseData);
                // } else {
                //     Log::info('Request failed with invalid signature');
                //     throw new Exception('Request failed with invalid signature');
                // }
            } else {
                throw new \Exception('Request failed with HTTP code ' . $httpCode);
            }
        } catch (Exception $e) {
            // Log the exception message
            Log::error('POST request failed: ' . $e->getMessage());

            // Optionally rethrow the exception or handle it
            throw $e;
        }
    }

    private function json_validator($data) { 
        if (!empty($data)) { 
            return is_string($data) &&  
              is_array(json_decode($data, true)) ? true : false; 
        } 
        return false; 
    } 

    public function generate_formated_uuid()
    {
        // Generate a UUID using Laravel's Str::uuid() method
        $uuid = Uuid::uuid4()->toString();
        return $uuid;
    }

    public function generate_signature($data)
    {
        try 
        {
            // Log::info('Starting for generating signature');

            // Log::info('Data to generate Signature: ' . $data);

            // Load the public key from the file
            $privateKeyPath = storage_path('keys/gotit/private_key.pem');
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

            Log::error('Signature: '. $signature);

            // Encode the signature to base64
            $encodedSignature = base64_encode($signature);
            
            Log::info('Encoded signature: '. $encodedSignature);

            //Log::info('The end for generating signature');

            return $encodedSignature;
        } catch (Exception $e) {
            // Log the exception message
            Log::error('Signature generation failed: ' . $e->getMessage());

            // Rethrow the exception to be handled by the calling code
            throw $e;
        }
    }
}
