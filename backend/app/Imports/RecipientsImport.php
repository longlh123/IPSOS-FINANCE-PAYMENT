<?php

namespace App\Imports;

use App\Models\User;
use App\Models\TradeUnionRecipientList;
use App\Models\TradeUnionRecipient;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Log;

class RecipientsImport implements ToModel, WithHeadingRow
{
    protected int $recipientListId;

    public function __construct(int $recipientListId)
    {
        $this->recipientListId = $recipientListId;
    }

    public function model(array $row)
    {
        Log::info('RecipientsImport row', $row);

        $email = trim($row['email'] ?? '');
        
        $user = User::where('email', $email)->first();

        if(!$user){
            Log::warning('RecipientsImport user not found', [
                'email' => $email,
                'row' => $row,
            ]);
            throw new \Exception("User không tồn tại: email={$email}");
        }

        Log::info([
            'user_id' => $user->id,
            'email' => $email,
        ]);

        return new TradeUnionRecipient([
            'recipient_list_id' => $this->recipientListId,
            'user_id' => $user->id,
            'channel' => 'gotit',
            'price' => $row['price']  ?? null,
            'status' => 'pending'
        ]);
    }
}
