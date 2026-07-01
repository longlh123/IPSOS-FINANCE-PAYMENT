# Codeframe — Feature Spec

## Mục tiêu

Module cho phép team IPSOS nhập dữ liệu câu trả lời mở (open-ended) từ file Excel, sau đó tiến hành gán mã (coding) cho từng câu trả lời theo một codeframe định nghĩa sẵn.

---

## Giai đoạn 1 — Import Data (bắt đầu ở đây)

### Giao diện

```
┌─────────────────────────────────────────────────────────┐
│  Codeframe                                              │
│                                                         │
│  ┌─────────────────────────────────────────────────┐   │
│  │  📁  Import Data                                 │   │
│  │  Chọn file Excel (.xlsx) để bắt đầu coding      │   │
│  │                                                  │   │
│  │            [ Import Data ]                       │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  (Bảng dữ liệu hiện ra sau khi import)                 │
└─────────────────────────────────────────────────────────┘
```

### Hành vi

1. User nhấn **Import Data** → mở file picker, chỉ nhận `.xlsx`.
2. File được gửi lên backend (multipart/form-data).
3. Backend parse → trả về danh sách rows dưới dạng JSON.
4. Frontend hiển thị dữ liệu trong `ReusableTable`.
5. Mỗi row có một cột **Action** để bắt đầu coding cho dòng đó (giai đoạn 2).

### Cấu trúc dữ liệu import (dự kiến)

| Cột | Kiểu | Mô tả |
|-----|------|--------|
| `id` | number | Row index tự sinh |
| `respondent_id` | string | ID respondent từ file |
| `response_text` | string | Câu trả lời mở |
| `code` | string \| null | Mã được gán (null khi chưa coding) |
| `coded_by` | string \| null | User thực hiện coding |
| `coded_at` | string \| null | Thời điểm coding |

---

## Giai đoạn 2 — Coding (kế tiếp)

- Panel bên phải hiện khi chọn 1 row: hiển thị `response_text` và danh sách code để chọn.
- Cho phép gán nhiều code cho 1 response (multi-code).
- Lưu kết quả coding theo từng user.

---

## Giai đoạn 3 — Export (kế tiếp)

- Nút **Export** tải file Excel kết quả đã có cột code.

---

## Việc cần làm — Giai đoạn 1

### Frontend

- [ ] `frontend/src/config/ApiConfig.ts` — thêm endpoint `POST /api/codeframe/import`
- [ ] `frontend/src/hook/useCodeframe.ts` — hook quản lý import state + data rows
- [ ] `frontend/src/pages/Codeframe/Codeframe.tsx` — UI: nút Import + bảng kết quả

### Backend

- [ ] `backend/app/Http/Requests/ImportCodeframeRequest.php` — validate file `.xlsx`
- [ ] `backend/app/Http/Controllers/CodeframeController.php` — `import()` method, parse Excel, trả JSON
- [ ] `backend/routes/api.php` — thêm route `POST /api/codeframe/import`

---

## API Contract

### `POST /api/codeframe/import`

**Request:** `multipart/form-data`

```
file: <xlsx file>
```

**Response `200`:**

```json
{
  "status_code": 200,
  "message": "Import thành công",
  "data": [
    {
      "id": 1,
      "respondent_id": "R001",
      "response_text": "Tôi thấy sản phẩm tốt",
      "code": null,
      "coded_by": null,
      "coded_at": null
    }
  ]
}
```

**Response `422`:**

```json
{
  "status_code": 422,
  "message": "File không hợp lệ",
  "data": null
}
```

---

## Ghi chú kỹ thuật

- Backend dùng `Maatwebsite\Excel` (đã có sẵn trong dự án) để parse `.xlsx`.
- Dữ liệu import **không cần lưu DB** ở giai đoạn 1 — trả thẳng từ memory (session hoặc response JSON).
- Nếu file lớn (> 500 rows) cân nhắc lưu tạm vào DB với `session_id` để coding nhiều phiên.
- Frontend dùng hook pattern `useCodeframe` theo đúng `ActionState` shape của dự án.
- Không dùng React Query; dùng Axios trong hook như các module khác.
