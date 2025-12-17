<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Province;
use App\Models\Role;
use App\Models\Team;

class EmployeeResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->last_name . " " . $this->first_name,
            'vinnet_total' => $this->vinnet_total,
            'gotit_total' => $this->gotit_total,
            'other_total' => $this->other_total,
            'transaction_total' => $this->transaction_total
        ];
    }
}
