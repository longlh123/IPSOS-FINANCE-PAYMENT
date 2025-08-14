<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use App\Models\ProjectGotItVoucherTransaction;
use App\Models\ProjectVinnetTransaction;

class InterviewURL
{
    public $internal_code;
    public $project_name;
    public $interviewer_id;
    public $interview_start;
    public $interview_end;
    public $shell_chainid;
    public $respondent_id;
    public $respondent_phone_number;
    public $province_id;
    public $price_level;
    public $channel;
    public $remember_token;

    public function __construct(array $splittedURL)
    {
        $this->internal_code = $splittedURL[0] ?? null;
        $this->project_name = $splittedURL[1] ?? null;
        $this->interviewer_id = $splittedURL[2] ?? null;
        $this->interview_start = $this->convertToDate($splittedURL[3]) ?? null;
        $this->interview_end = $this->convertToDate($splittedURL[4]) ?? null;
        $this->shell_chainid = $splittedURL[5] ?? null;
        $this->respondent_id = $splittedURL[6] ?? null;
        $this->respondent_phone_number = $splittedURL[7] ?? null;
        $this->province_id = $splittedURL[8] ?? null;
        $this->price_level = $splittedURL[9] ?? null;
        $this->channel = $splittedURL[10] ?? null;
        $this->remember_token = implode('', array_slice($splittedURL, 11)) ?? null;
        
        // Validate the properties
        $this->validateProperties();

        try{
            $this->employee = $this->findEmployee();
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }

        $this->logDetails();
    }

    protected function validateProperties()
    {
        $properties = [
            'internal_code' => $this->internal_code,
            'project_name' => $this->project_name,
            'interviewer_id' => $this->interviewer_id,
            'interview_start' => $this->interview_start,
            'interview_end' => $this->interview_end,
            'shell_chainid' => $this->shell_chainid,
            'respondent_id' => $this->respondent_id,
            'respondent_phone_number' => $this->respondent_phone_number,
            'province_id' => $this->province_id,
            'price_level' => $this->price_level,
            'chancel' => $this->channel,
        ];

        foreach ($properties as $key => $value) {
            if (empty($value)) {
                Log::info("The property '{$key}' is either null or has a length of 0.");
                throw new \Exception(ProjectVinnetTransaction::STATUS_TRANSACTION_FAILED);
            }
            if ($key === 'shell_chainid' || $key === 'respondent_id' || $key === 'interviewer_id') {
                
                if (strtolower($value) === 'null'){
                    Log::info("The property '{$key}' is either null or has a length of 0.");
                    throw new \Exception(ProjectVinnetTransaction::STATUS_TRANSACTION_FAILED);
                }
            }
        }
    }

    protected function logDetails()
    {
        Log::info('Internal Code: ' . $this->internal_code
            . ', Project Name: ' . $this->project_name
            . ', Interviewer ID: ' . $this->interviewer_id
            . ', Respondent ID: ' . $this->respondent_id
            . ', Interview Start: ' . $this->interview_start->format('Y-m-d H:i:s')
            . ', Interview End: ' . $this->interview_end->format('Y-m-d H:i:s')
            . ', Shell Chainid: ' . $this->shell_chainid
            . ', InstanceId: ' . $this->respondent_id
            . ', Resp. Phone Number: ' . $this->respondent_phone_number
            . ', Province ID: ' . $this->province_id
            . ', Price Level: ' . $this->price_level
            . ', Channel: ' . $this->channel
            . ', Token: ' . $this->remember_token);
    }

    function convertToDate($dateString) {
        // Check if the string length is 14 characters
        if (strlen($dateString) !== 14) {

            Log::info('Invalid date string length.');
            throw new \Exception(ProjectVinnetTransaction::STATUS_TRANSACTION_FAILED);
        }
    
        // Extract date and time components
        $day = substr($dateString, 0, 2);
        $month = substr($dateString, 2, 2);
        $year = substr($dateString, 4, 4);
        $hours = substr($dateString, 8, 2);
        $minutes = substr($dateString, 10, 2);
        $seconds = substr($dateString, 12, 2);
    
        // Create a formatted date string
        $formattedDateString = "{$year}-{$month}-{$day} {$hours}:{$minutes}:{$seconds}";
    
        // Create DateTime object
        try {
            $date = new \DateTime($formattedDateString);
            return $date;
        } catch (\Exception $e) {
            Log::info('Invalid date format.');
            throw new \Exception(ProjectVinnetTransaction::STATUS_TRANSACTION_FAILED);
        }
    }

    protected function findEmployee()
    {
        $employee = Employee::where('employee_id', $this->interviewer_id)->first();

        if($employee === null){
            throw new \Exception(Project::ERROR_INTERVIEWER_ID_NOT_REGISTERED);
        }

        return $employee;
    }
}