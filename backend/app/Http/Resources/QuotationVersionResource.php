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
    private function normalizeFeedbacks(array $feedbacks): array
    {
        $normalized = [];
        foreach ($feedbacks as $section => $value) {
            $normalized[$section] = $this->normalizeThread($value);
        }
        return $normalized;
    }

    private function normalizeThread(array $thread): array
    {
        if (array_is_list($thread)) {
            return $thread;
        }

        $result = [];

        if (isset($thread['content'])) {
            $result[] = [
                'type'       => 'feedback',
                'content'    => $thread['content'],
                'user_id'    => $thread['user_id'] ?? 0,
                'user_name'  => $thread['user_name'] ?? '',
                'created_at' => $thread['updated_at'] ?? now()->toIso8601String(),
            ];
        }

        $numericKeys = array_filter(array_keys($thread), 'is_int');
        sort($numericKeys);
        foreach ($numericKeys as $key) {
            $result[] = $thread[$key];
        }

        return $result;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'status' => $this->status,
            'quotation_data' => $this->data,
            'feedbacks' => $this->normalizeFeedbacks($this->feedbacks ?? []),
            'created_user' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email
            ],
            'created_at' => $this->created_at,
            'fm_confirmed_user' => $this->fmConfirmer ? [
                'id'    => $this->fmConfirmer->id,
                'name'  => $this->fmConfirmer->name,
                'email' => $this->fmConfirmer->email,
            ] : null,
            'fm_confirmed_at' => $this->fm_confirmed_at,
            'approved_user' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email
            ] : null,
            'approved_at' => $this->approved_at
        ];
    }
}
