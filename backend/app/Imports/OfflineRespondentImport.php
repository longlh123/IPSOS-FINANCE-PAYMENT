<?php

namespace App\Imports;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\{
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows,
    SkipsOnError,
    SkipsErrors,
    SkipsOnFailure,
    SkipsFailures
};

class OfflineRespondentImport implements 
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows,
    SkipsOnError,
    SkipsOnFailure
{
    //Import vẫn chạy dù có dòng lỗi
    use SkipsErrors, SkipsFailures;

    protected string $batchId;

    public function __construct(int $project_id, string $batch_id)
    {
        $this->batchId = $batch_id;
    }

    public function model(array $row)
    {
        $row = array_change_key_case($row, CASE_LOWER);

        return new CATIRespondent([
            'project_id',
            'location_id',
            'shell_chainid',
            'respondent_id',
            'employee_id',
            'province_id',
            'interview_start',
            'interview_end',
            'respondent_phone_number',
            'phone_number',
            'email',
            'service_type',
            'delivery_method',
            'service_code',
            'reject_message',
            'price_level',
            'channel',
            'status',
            'environment',
        ]);
    }
}
