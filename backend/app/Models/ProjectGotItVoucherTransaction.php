<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGotItVoucherTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING_VERIFICATION = 'Giao dịch đang chờ xác thực.';
    const STATUS_VOUCHER_SUCCESS = 'Voucher được cập nhật thành công.';
    const STATUS_PRODUCT_NOT_ALLOWED = 'Product ID này không được phép request.';
    const STATUS_MIN_VOUCHER_E_VALUE = 'Price ID này không được phép request.';
    const STATUS_TRANSACTION_ALREADY_EXISTS = 'Voucher Ref Id đã tồn tại/ trùng lặp.';
    const STATUS_SIGNATURE_INCORRECT = 'Chữ ký không hợp lệ.';
    const STATUS_API_INCORRECT = 'API không hợp lệ.';
    const STATUS_ORDER_LIMIT_EXCEEDED = 'Giới hạn đơn hàng đã hết.';
    const STATUS_OPT_INCORRECT = 'OPT không hợp lệ.';
    const STATUS_QUANTITY_INCORRECT = 'Quantity không hợp lệ.';

    const STATUS_TRANSACTION_FAILED = 'Giao dịch được thực hiện không qua quá trình phỏng vấn.';
    const STATUS_TRANSACTION_TEST = 'Giao dịch test đang được thực hiện';

    protected $table = 'project_gotit_voucher_transactions';

    protected $fillable = [
        'project_respondent_id',
        'transaction_ref_id',
        'transaction_ref_id_order',
        'expiry_date',
        'order_name',
        'amount',
        'voucher_link_group',
        'voucher_link_code_group',
        'voucher_serial_group',
        'voucher_code',
        'voucher_link',
        'voucher_link_code',
        'voucher_image_link',
        'voucher_cover_link',
        'voucher_serial',
        'voucher_expired_date',
        'voucher_product_id',
        'voucher_price_id',
        'voucher_value',
        'voucher_status',
        'invoice_date'
    ];

    public function respondent()
    {
        return $this->belongsTo(ProjectRespondent::class, 'project_respondent_id');
    }

    public function gotitSMSTransaction()
    {
        return $this->hasOne(ProjectGotItSMSTransaction::class, 'voucher_transaction_id');
    }

    public function createGotitSMSTransaction(array $data)
    {
        return $this->gotitSmsTransaction()->create($data);
    }
}
