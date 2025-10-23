<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Province;
use App\Models\User;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if($request->get('type') === 'metadata'){
            return [
                'id' => $this->id,
                'internal_code' => $this->internal_code,
                'project_name' => $this->project_name
            ];
        }

        return [
            'id' => $this->id,
            'internal_code' => $this->internal_code,
            'project_name' => $this->project_name,
            'symphony' => $this->projectDetails->symphony ?? null,
            'job_number' => $this->projectDetails->job_number ?? null,
            'status' => $this->projectDetails->status ?? null,
            'platform' => $this->projectDetails->platform ?? null,
            'planned_field_start' => $this->projectDetails->planned_field_start ?? null,
            'planned_field_end' => $this->projectDetails->planned_field_end ?? null,
            'actual_field_start' => $this->projectDetails->actual_field_start ?? null,
            'actual_field_end' => $this->projectDetails->actual_field_end ?? null,
            'remember_token' => $this->projectDetails->remember_token ?? null,
            'remember_uuid' => $this->projectDetails->remember_uuid ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_user_id' => $this->projectDetails->createdBy->userDetails,
            'project_types' => $this->projectTypes->map(function($projectType){
                return $projectType->name;
            }),
            'teams' => $this->teams->map(function($team){
                return $team->name;
            }),
            'permissions' => $this->projectPermissions->map(function($projectPermission){
                return User::where('id', $projectPermission->user_id)->pluck('email')[0];
            }),
            'provinces' => $this->projectProvinces->map(function($item){
                return [
                    'id' => $item->province_id,
                    'name' => Province::where('id', $item->province_id)->pluck('name')[0],
                    'sample_size_main' => $item->sample_size_main,
                    'price_main' => $item->price_main,
                    'sample_size_booters' => $item->sample_size_booters,
                    'price_boosters' => $item->price_boosters
                ];
            })->values(),
        ];
    }
}
