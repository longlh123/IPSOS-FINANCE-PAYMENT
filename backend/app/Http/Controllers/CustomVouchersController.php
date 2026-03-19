<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\CustomVoucher;

class CustomVouchersController extends Controller
{
    public function assignVoucher(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $request->validate([
                'respondent_id' => 'string|required'
            ]);

            //Check đã có voucher chưa?
            $existing = CustomVoucher::where('respondent_id', $request->respondent_id)->first();

            if($existing){
                DB::commit();
                return response()->json([
                    'status_code' => 200,
                    'voucher' => $existing
                ]);
            }

            $voucher = CustomVoucher::where('status', 'unused')
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
            $voucher->respondent_id = $request->respondent_id;
            $voucher->sent_at = now();
            $voucher->save();

            $qr = base64_encode(
                QrCode::format('png')->size(300)->generate($voucher->code)
            );

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'uuid' => $voucher->uuid,
                'qr' => $qr
            ]);
        } catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'error' => 'Server error - ' . $e->getMessage()
            ]);
        }
    }
}
