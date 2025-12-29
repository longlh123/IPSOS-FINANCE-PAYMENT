<?php

namespace App\Services;

use App\Models\ProjectRespondent;
use App\Models\ProjectRespondentToken;
use App\Constants\TransactionStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class ProjectRespondentTokenService
{
    private function generate_formated_uuid()
    {
        // Generate a UUID using Laravel's Str::uuid() method
        $uuid = Uuid::uuid4()->toString();
        return $uuid;
    }

    public function createOrReuseToken(ProjectRespondent $projectRespondent){

        $resultToken = $projectRespondent->token;

        if($resultToken){
            if($resultToken->status === 'blocked'){
                throw new \Exception(TransactionStatus::STATUS_EXPIRED);
            }

            // Nếu token đã hết hạn → block luôn
            if ($resultToken->expires_at->isPast()) {

                $resultToken->update([
                    'status' => 'blocked'
                ]);

                throw new \Exception(TransactionStatus::STATUS_EXPIRED);
            }

            $secret = Str::random(40);

            $resultToken->update([
                'token_hash' => Hash::make($secret),
            ]);

            return $resultToken->token_public . '.' . $secret;
        }

        $public = $this->generate_formated_uuid();

        $secret = Str::random(40);

        $projectRespondent->token()->create([
            'project_respondent_id' => $projectRespondent->id,
            'token_public' => $public,
            'token_hash' => Hash::make($secret),
            'attempts' => 0, 
            'expires_at' => now()->addHours(24),
            'status' => 'active'
        ]);

        return $public . '.' . $secret;
    }

    public function verifyToken(string $token)
    {
        if (! str_contains($token, '.')) {
            throw new \Exception(TransactionStatus::STATUS_INVALID);
        }
        
        [$public, $secret] = explode('.', $token);

        $record = ProjectRespondentToken::where('token_public', $public)
                                            ->where('status', 'active')
                                            ->first();

        if(!$record){
            throw new \Exception(TransactionStatus::STATUS_INVALID, 501);
        }

        if($record->status === 'blocked'){
            throw new \Exception(TransactionStatus::STATUS_EXPIRED, 502);
        }

        if($record->expires_at->isPast()){
            $record->update([
                'status' => 'blocked'
            ]);
            throw new \Exception(TransactionStatus::STATUS_EXPIRED, 502);
        }

        if($record->attempts >= 3){
            $record->update([
                'status' => 'blocked'
            ]);
            throw new \Exception(TransactionStatus::STATUS_SUSPENDED, 503);
        }

        if(!Hash::check($secret, $record->token_hash)){
            $record->increment('attempts');
            throw new \Exception(TransactionStatus::STATUS_INVALID, 501);
        }

        return $record;
    }
}