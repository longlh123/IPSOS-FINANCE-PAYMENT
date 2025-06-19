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
            'date_of_birth' => $this->date_of_birth,
            'address' => $this->address,
            'province' => Province::where('id', $this->province_id)->pluck('name')[0],
            'phone_number' => $this->phone_number,
            'profile_picture' => $this->profile_picture,
            'tax_code' => $this->tax_code,
            'tax_deduction_at' => $this->tax_deduction_at,
            'card_id' => $this->card_id,
            'citizen_identity_card' => $this->citizen_identity_card,
            'date_of_issuance' => $this->date_of_issuance,
            'place_of_issuance' => $this->place_of_issuance,
            'role' => Role::where('id', $this->role_id)->pluck('name')[0],
            'team' => Team::where('id', $this->team_id)->pluck('name')[0],
        ];
    }
}
