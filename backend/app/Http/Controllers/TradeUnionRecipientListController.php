<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TradeUnionRecipientList;
use App\Http\Resources\TradeUnionRecipientListResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\GiftNotificationMail;

class TradeUnionRecipientListController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $search = $request->input('searchTerm');

        $query = TradeUnionRecipientList::query()
            ->select(
                'trade_union_recipient_lists.id', 
                'trade_union_recipient_lists.name', 
                'trade_union_recipient_lists.description',
                DB::raw('COUNT(trade_union_recipients.id) as recipients_count')
            )
            ->join('trade_union_recipients', 'trade_union_recipients.recipient_list_id', '=', 'trade_union_recipient_lists.id')
            ->groupBy(
                'trade_union_recipient_lists.id', 
                'trade_union_recipient_lists.name', 
                'trade_union_recipient_lists.description'
            );

        if($search){
            $query->where('name', 'like', '%' . $search . '%');
        }

        $recipientLists = $query->paginate($perPage);

        return response()->json([
            'status_code' => 200,
            'message' => 'List of recipient lists requested successfully',
            'data' => TradeUnionRecipientListResource::collection($recipientLists),
            'meta' => [
                'current_page' => $recipientLists->currentPage(),
                'per_page' => $recipientLists->perPage(),
                'total' => $recipientLists->total(),
                'last_page' => $recipientLists->lastPage(),
            ]
        ]);
    }

    public function sendEmail(Request $request, $id)
    {
        $recipientList = TradeUnionRecipientList::with(['recipients.user'])
            ->findOrFail($id);

        $sent = 0;

        foreach ($recipientList->recipients as $recipient) {
            if (! $recipient->user || empty($recipient->user->email)) {
                continue;
            }

            Mail::to($recipient->user->email)
                ->send(new GiftNotificationMail($recipientList, $recipient));

            $sent++;
        }

        return response()->json([
            'status_code' => 200,
            'message' => "Đã gửi email cho {$sent} người trong danh sách.",
        ]);
    }

}
