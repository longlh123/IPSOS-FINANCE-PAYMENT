<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationVersionResource extends JsonResource
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
            'version' => $this->version,
            'status' => $this->status,
            'quotation' => $this->data,
            'created_user' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email
            ],
            'created_at' => $this->created_at,
            'approved_user' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email
            ] : null,
            'approved_at' => $this->approved_at
        ];
    }
}
