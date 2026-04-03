@component('mail::message')
# {{ $title }}

Chào Anh/Chị,

Công Đoàn IPSOS Việt Nam trân trọng gửi đến Anh/Chị **voucher hỗ trợ xăng xe trị giá {{ number_format($amount, 0, ',', '.') }} VND**, với thông điệp:

@component('mail::panel', ['style' => 'background-color:#f0f8ff; color:#000; font-size:15px; font-weight:bold; text-align:center; padding:10px 10px; border-radius:6px;'])
Công Đoàn Ipsos Việt Nam - Đồng hành cùng đoàn viên trên mỗi hành trình
@endcomponent

**Thông tin chi tiết voucher:**

- **Mức hỗ trợ:** {{ number_format($amount, 0, ',', '.') }} VND  
- **Hình thức nhận:** Voucher Gotit  
- **Hướng dẫn sử dụng:** Nhấn nút **"Nhận quà"** bên dưới và làm theo hướng dẫn từ Gotit để nhận hỗ trợ.

@if($voucherLink)
@component('mail::button', ['url' => $voucherLink, 'color' => 'primary'])
Nhận quà
@endcomponent
@endif

Cảm ơn Anh/Chị đã đồng hành cùng Công Đoàn IPSOS Việt Nam.  

Trân trọng,  
**{{ config('app.name') }}**
@endcomponent