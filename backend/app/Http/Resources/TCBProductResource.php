<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;

class TCBProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = [];
        
        foreach($this->resource as $item) {
            $bankName = $item->bank_name;
            
            if(!isset($result[$bankName])) {
                $result[$bankName] = [
                    'category' => $bankName,
                    'attributes' => []
                ];
            }

            $result[$bankName]['attributes'][] = [
                'name' => $item->product_name,
                'value' => intval($item->total) 
            ];
        }

        return array_values($result);
    }
}
