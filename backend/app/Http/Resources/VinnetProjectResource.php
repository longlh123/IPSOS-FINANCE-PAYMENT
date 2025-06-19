<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VinnetProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'vinnet_payservice_requuid' => $this->vinnet_payservice_requuid,
            'project_id' => $this->project_id,
            'internal_code' => $this->project->internal_code,
            'project_name' => $this->project->project_name,
            'employee_id' => $this->employee->employee_id,
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'role' => $this->employee->role->name,
            'team' => $this->employee->team->name,
            'shell_chainid' => $this->shell_chainid,
            'respondent_id' => $this->respondent_id,
            'province' => $this->province->name,
            'interview_start' => $this->interview_start,
            'interview_end' => $this->interview_end,
            'respondent_phone_number' => $this->respondent_phone_number,
            'phone_number' => $this->phone_number,
            'vinnet_token_status' => $this->vinnet_token_status,
            'status' => $this->status,
            'total_amt' => $this->total_amt,
            'commission' => $this->commission,
            'discount' => $this->discount,
            'payment_amt' => $this->payment_amt,
            'created_at' => $this->created_at,
            'vinnet_invoice_date' => $this->vinnet_invoice_date,
        ];
    }
}
