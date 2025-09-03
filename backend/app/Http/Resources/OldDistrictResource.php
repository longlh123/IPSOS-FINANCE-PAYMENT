<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OldDistrictResource extends JsonResource
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
            'area_code' => $this->area_code,
            'region-name' => $this->region->name,
            'districts' => $this->districts->map(function($district) {
                return array(
                    'id' => $district->id,
                    'name' => $district->name,
                    'code' => $district->code,
                    'land_area' => $district->land_area,
                    'population' => $district->population,
                    'wards' => $district->wards->map(function($ward) {
                        return array(
                            'id' => $ward->id,
                            'name' => $ward->name,
                            'code' => $ward->code,
                            'land_area' => $ward->land_area,
                            'population' => $ward->population
                        );
                    })
                );
            })
        ];
    }
}
