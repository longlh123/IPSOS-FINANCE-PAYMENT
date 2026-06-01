<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Role;
use App\Models\Department;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'first_name'    => $this->userDetails->first_name,
            'last_name'     => $this->userDetails->last_name,
            'date_of_birth' => $this->userDetails->date_of_birth,
            'address'       => $this->userDetails->address,
            'role'          => $this->userDetails->role->name,
            'role_id'       => $this->userDetails->role_id,
            'department'    => $this->userDetails->department->name,
            'department_id' => $this->userDetails->department_id,
        ];
    }
}
