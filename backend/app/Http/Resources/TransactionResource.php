<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    private function maskPhone($phone)
    {
        if (!$phone || strlen($phone) < 7) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -3);
    }

    private function maskServiceCode($serviceCode, $phone)
    {
        $serviceCodes = [
            "S0002" => "Viettel",
            "S0003" => "MobiFone",
            "S0028" => "VinaPhone",
            "S0033" => "I-Telecom",
            "S0031" => "Wintel",
            "S0029" => "VietnameMobile",
            "S0030" => "Gmobile"
        ];

        $cardServiceCodes = [
            "S0004" => "Viettel",
            "S0012" => "MobiFone",
            "S0014" => "VinaPhone",
            "S0014" => "I-Telecom",
            "S0015" => "Wintel",
            "S0013" => "VietnameMobile",
            "S0011" => "Gmobile"
        ];

        if($serviceCode === 'S0002' || $serviceCode === 'S0004'){
            return "Viettel";
        }elseif($serviceCode === 'S0003' || $serviceCode === 'S0012'){
            return "MobiFone";
        }elseif($serviceCode === 'S0031' || $serviceCode === 'S0015'){
            return "Wintel";
        }elseif($serviceCode === 'S0029' || $serviceCode === 'S0013'){
            return "VietnameMobile";
        }elseif($serviceCode === 'S0030' || $serviceCode === 'S0011'){
            return "Gmobile";
        }elseif($serviceCode === 'S0028'){
            return "VinaPhone";
        }elseif($serviceCode === 'S0014'){
            if(substr($phone, 0, 3) === '087'){
                return "I-Telecom";
            } else {
                return "VinaPhone";
            }
        }else{
            return "Other";
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->transaction_id,
            'transaction_id' => $this->transaction_id,
            'symphony' => $this->symphony,
            'internal_code' => $this->internal_code,
            'project_name' => $this->project_name,
            'province_name' => $this->province_name,
            'employee_id' => $this->employee_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->last_name . " " . $this->first_name,
            'interview_start' =>$this->interview_start,
            'interview_end' => $this->interview_end,
            'shell_chainid' => $this->shell_chainid,
            'respondent_id' => $this->respondent_id,
            'respondent_phone_number' => $this->maskPhone($this->respondent_phone_number),
            'phone_number' => $this->maskPhone($this->phone_number),
            'project_respondent_status' => $this->project_respondent_status,
            'channel' => $this->channel,
            'reject_message' => $this->reject_message,
            'service_code' => $this->maskServiceCode($this->service_code, $this->phone_number),
            'amount' => $this->amount,
            'discount' => $this->discount,
            'payment_amt' => $this->payment_amt,
            'payment_pre_tax' => $this->payment_pre_tax,
            'transaction_status' => $this->transaction_status,
            'created_at' => $this->created_at
        ];
    }
}
