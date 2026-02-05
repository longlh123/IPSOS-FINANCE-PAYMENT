<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Province;
use App\Models\Employee;
use App\Models\ProjectRespondentToken;

class ProjectRespondentResource extends JsonResource
{
    private function mapStatusToFrontEnd(string $status): string
    {
        return match($status){
            'Đã nhận quà.' => 'success',
            'Đang chờ xử lý kết quả khảo sát / đợi xác nhận điều kiện nhận quà.' => 'pending',
            'Đáp viên từ chối nhận quà.' => 'refused',
            default => 'failed',
        };
    }

    private function getToken(string $id): string
    {
        $public = ProjectRespondentToken::where('project_respondent_id', $id)->value('token_public');
        $batch_id = ProjectRespondentToken::where('project_respondent_id', $id)->value('batch_id');

        return $public . '.' . $batch_id;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'respondent_id' => $this->respondent_id,
            'shell_chainid' => $this->shell_chainid,
            'province_name' => Province::where('id', $this->province_id)->pluck('name')[0],
            'employee_id' => Employee::where('id', $this->employee_id)->pluck('employee_id')[0],
            'respondent_phone_number' => $this->respondent_phone_number,
            'phone_number' => $this->phone_number,
            'status_label' => $this->status,
            'status' => $this->mapStatusToFrontEnd($this->status),
            'environment' => $this->environment,
            'token' => $this->getToken($this->id),
        ];
    }


}
