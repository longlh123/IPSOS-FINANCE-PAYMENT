<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\CustomVoucher;
use App\Models\CustomVoucherToken;
use App\Models\CustomVoucherTokenLog;
use App\Models\Employee;

class CustomVouchersController extends Controller
{
    public function authenticateToken(Request $request)
    {
        $employeeId = $request->employee_id ?? null;
        $phoneNumber = $request->phone_number ?? null;
        $batchId = $request->batch_id ?? null;
        $link = $request->link ?? null;
        $data = $request->data ?? null;

        Log::info($data);

        $employee = Employee::where('employee_id', $employeeId)->first();

        if(!$employee){
            return response()->json([
                'status_code' => 400,
                'error' => 'Interviewer ID không tồn tại. Vui lòng kiểm tra.'
            ]);
        }

        if(!$phoneNumber || !$batchId || !$link){
            return response()->json([
                'status_code' => 400,
                'error' => 'Phone Number, BatchId and Link should not be blank.'
            ]);
        }

        $existingPhoneNumber = CustomVoucherToken::where('phone_number', $phoneNumber)->first();

        if($existingPhoneNumber){
            return response()->json([
                'status_code' => 400,
                'error' => 'Số điện thoại này đã tham gia khảo sát trước đó. Vui lòng kiểm tra.'
            ]);
        }

        $token = Str::random(64);

        $customVoucherToken = CustomVoucherToken::create([
            'employee_id' => $employee->id,
            'phone_number' => $phoneNumber,
            'token' => $token,
            'attempts' => 0, 
            'expires_at' => now()->addHours(24),
            'batch_id' => $batchId,
            'link' => $link,
            'status' => 'active'
        ]);

        if($data){
            if(is_string($data)){
                $data = json_decode($data);
            }

            $dataArr = [];

            foreach($data as $key => $value){
                $dataArr[] = $key . '=' . $value;
            }

            $dataBase64 = base64_encode(implode(';', $dataArr));

            $query = http_build_query([
                'id' => $customVoucherToken->id,
                'I.User1' => $customVoucherToken->token,
                'I.User2' => $dataBase64
            ]);
        } else {
            $query = http_build_query([
                'id' => $customVoucherToken->id,
                'I.User1' => $customVoucherToken->token
            ]);
        }
        
        $linkFinal = $link . '?' . $query;

        return response()->json([
            'status_code' => 200,
            'respondent_id' => $customVoucherToken->id,
            'link' => $linkFinal
        ]);
    }

    public function assignVoucher(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $tokenStr = $request->token ?? null;

            if (!$tokenStr) {
                throw new \Exception('Missing token');
            }
            
            $token = CustomVoucherToken::where('token', $tokenStr)
                        ->lockForUpdate()
                        ->first();

            if(!$token){
                throw new \Exception('Invalid token');
            }

            if($token->status !== 'active'){
                throw new \Exception('Token is blocked');
            }

            if($token->expires_at && $token->expires_at < now()){
                throw new \Exception('Token expired');
            }

            $existing = CustomVoucher::where('token_id', $token->id)->first();

            if($existing){
                DB::commit();

                $qr = Builder::create()
                    ->writer(new PngWriter())
                    ->data($existing->code)        // nội dung QR
                    ->encoding(new Encoding('UTF-8'))
                    ->size(300)                   // kích thước
                    ->margin(10)
                    ->build();

                // chuyển sang base64
                $qrBase64 = base64_encode($qr->getString());
                
                return response()->json([
                    'status_code' => 200,
                    'uuid' => $existing->uuid,
                    'qr' => $qrBase64
                ]);
            } 

            $voucher = CustomVoucher::whereNull('token_id')
                                ->where('batch_id', $token->batch_id)
                                ->where('status', 'unused')
                                ->where(function($q) {
                                    $q->whereNull('expired_to')
                                        ->orWhere('expired_to', '>=', now());
                                })
                                ->lockForUpdate()
                                ->first();

            if(!$voucher){
                DB::rollBack();

                return response()->json([
                    'status_code' => 400,
                    'error' => 'Out of vouchers'
                ]);
            }

            //Assign 
            if(!$voucher->uuid){
                $voucher->uuid = Str::uuid();
            }

            $voucher->status = 'used';
            $voucher->token_id = $token->id;
            $voucher->sent_at = now();
            $voucher->save();

            $token->attempts += 1;

            if ($token->attempts > 5) {
                $token->status = 'blocked';
                $token->save();

                DB::rollBack();
                throw new \Exception('Token blocked due to too many attempts');
            }

            $token->save();

            $qr = Builder::create()
                ->writer(new PngWriter())
                ->data($voucher->code)        // nội dung QR
                ->encoding(new Encoding('UTF-8'))
                ->size(300)                   // kích thước
                ->margin(10)
                ->build();

            // chuyển sang base64
            $qrBase64 = base64_encode($qr->getString());

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'uuid' => $voucher->uuid,
                'qr' => $qrBase64
            ]);
        } catch(\Exception $e){
            Log::error($e);

            DB::rollBack();

            return response()->json([
                'status_code' => 400,
                'error' => 'Server error - ' . $e->getMessage()
            ]);
        }
    }

    public function searchLink(Request $request)
    {
         DB::beginTransaction();

        try{
            $employeeId = $request->employee_id ?? null;
            $phoneNumber = $request->phone_number ?? null;

            if(!$employeeId || !$phoneNumber){
                throw new \Exception("Mã số PVV và Số điện thoại đáp viên không được trống.");
            }
            
            $employee = Employee::where('employee_id', strtoupper($employeeId))->first();

            if(!$employee){
                throw new \Exception("Mã số PVV không tồn tại.");
            }

            $customVoucherToken = CustomVoucherToken::where('employee_id', $employee->id)
                                        ->where('phone_number', $phoneNumber)
                                        ->first();

            if(!$customVoucherToken){
                throw new \Exception('Không tìm thấy thông tin.');
            }

            $query = http_build_query([
                'id' => $customVoucherToken->id,
                'I.User1' => $customVoucherToken->token
            ]);

            $linkFinal = $customVoucherToken->link . '?' . $query;

            $qr = Builder::create()
                ->writer(new PngWriter())
                ->data($linkFinal)        // nội dung QR
                ->encoding(new Encoding('UTF-8'))
                ->size(300)                   // kích thước
                ->margin(10)
                ->build();

            // chuyển sang base64
            $qrBase64 = base64_encode($qr->getString());

            CustomVoucherTokenLog::create([
                'token_id' => $customVoucherToken->id
            ]);

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'qr' => $qrBase64
            ]);

        } catch(\Exception $e){
            DB::rollBack();

            return response()->json([
                'status_code' => 400,
                'error' => 'Server error - ' . $e->getMessage()
            ]);
        }
    }
}
