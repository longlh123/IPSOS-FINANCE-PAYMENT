<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
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
            'code' => $this->code,
            'land_area' => $this->land_area,
            'population' => $this->population,
            'old-provinces' => $this->provinces->map(function($item){
                return array(
                        'id' => $item->id,
                        'name' => $item->name,
                        'area_code' => $item->area_code,
                        'region-name' => $item->region->name
                );
            })
        ];
    }
}
