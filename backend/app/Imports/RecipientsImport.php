<?php

namespace App\Imports;

use App\Models\User;
use App\Models\TradeUnionRecipientList;
use App\Models\TradeUnionRecipient;
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

class RecipientsImport implements 
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

    protected int $recipientListId;

    public function __construct(int $recipientListId)
    {
        $this->recipientListId = $recipientListId;
    }

    public function model(array $row)
    {
        // Log::info('RecipientsImport row', $row);

        $email = trim($row['email'] ?? '');
        
        $user = User::where('email', $email)->first();

        if(!$user){
            Log::warning('RecipientsImport user not found', [
                'email' => $email,
                'row' => $row,
            ]);
            throw new \Exception("User không tồn tại: email={$email}");
        }

        // Log::info([
        //     'user_id' => $user->id,
        //     'email' => $email,
        // ]);

        return new TradeUnionRecipient([
            'recipient_list_id' => $this->recipientListId,
            'user_id' => $user->id,
            'channel' => 'gotit',
            'price' => $row['price']  ?? null,
            'status' => 'pending'
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
