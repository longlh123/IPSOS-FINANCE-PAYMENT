<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CATIBatchResource extends JsonResource
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
            'name' => $this->name,
            'status' => $this->status,
            'total_records' => $this->total_records,
            'to_used' => $this->respondents()
                            ->where('status', '!=', 'New')
                            ->exists(),
            'created_user_name' => $this->createdBy?->email
        ];
    }
}