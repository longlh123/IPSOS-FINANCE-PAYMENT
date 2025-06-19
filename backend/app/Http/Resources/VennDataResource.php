<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VennDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dataCount = [];

        foreach ($this->resource as $item) {
            // Accessing the property with the object operator (->)
            if (!isset($dataCount[$item->product_ids])) {
                $dataCount[$item->product_ids] = 0;
            }

            // You might want to increment the count or perform other logic here
            $dataCount[$item->product_ids]++;
        }

        $vennData = [];

        // Transform the data
        foreach ($dataCount as $key => $value) {
            // Split the key into an array of sets
            $sets = explode(',', $key);
            
            // Create the new format and add it to the result array
            $vennData[] = [
                'sets' => $sets,
                'size' => $value
            ];
        }

        return $vennData;
    }
}
