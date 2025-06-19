<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Role;
use App\Models\Department;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            'first_name' => $this->userDetails->first_name,
            'last_name' => $this->userDetails->last_name,
            // 'date_of_birth' => $this->userDetails->date_of_birth,
            // 'address' => $this->userDetails->address,
            'role' => Role::where('id', $this->userDetails->role_id)->pluck('name')[0],
            // 'department' => Department::where('id', $this->userDetails->department_id)->pluck('name')[0],
        ];
    }
}
