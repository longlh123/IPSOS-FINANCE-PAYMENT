# Flow: Tạo Dự Án & Quotation

## Tổng quan

```
Researcher tạo Project
        │
        ▼
  Quotation v1 (draft) auto-created
        │
        ▼
  Researcher assign users vào dự án  ← bắt buộc trước khi submit
        │
        ▼
  Researcher điền/chỉnh quotation
  (có thể tạo nhiều version song song)
        │
        ▼
  Submit quotation → Notification gửi đến tất cả assigned users
        │
        ▼
  Field Manager review, gửi feedback cho Researcher
  Researcher respond (resolve / reject) từng feedback
        │
        ▼
  Field Manager xác nhận (FM Confirm) — chỉ khi KHÔNG còn pending feedback
        │                   
        ▼                   
  Finance / Admin approve quotation
        │
        ▼
    [approved]
```

---

## Bước 1 — Tạo Project

**Ai làm:** `Admin` hoặc `Researcher`

**Endpoint:** `POST /api/project-management/projects/store`

**Điều gì xảy ra:**
- Project được tạo với internal code dạng `{YEAR}-{ID}` (vd: `2025-0001`)
- `ProjectDetails` được tạo kèm theo (platform, planned_field_start/end)
- Người tạo được tự động attach vào `projectPermissions`
- **Quotation v1 (status: `draft`) được tạo tự động** với data ban đầu từ project

**Payload:**
```json
{
  "project_name": "...",
  "platform": "online|offline|...",
  "planned_field_start": "YYYY-MM-DD",
  "planned_field_end": "YYYY-MM-DD",
  "project_types": [1, 2],
  "teams": [3]
}
```

---

## Bước 2 — Assign Users vào Dự Án

**Ai làm:** `Admin`, `Researcher`, `Scripter`, `Field Manager`

**Endpoint:** `PUT /api/project-management/projects/{projectId}/assign-users`

**Lưu ý quan trọng:** Bước này **bắt buộc trước khi submit** quotation.  
Nếu `projectPermissions.count === 1` (chỉ có người tạo), submit sẽ bị từ chối với lỗi 422.

---

## Bước 3 — Làm việc với Quotation

**Xem danh sách versions:** `GET /api/project-management/projects/{projectId}/quotation/versions`

> - **Researcher / Admin**: thấy tất cả status
> - **Field Manager / Finance / Scripter**: thấy `submitted`, `fm_confirmed`, `approved`

### Phân quyền theo role

| Action | Admin | Finance | Researcher | Field Manager |
|---|:---:|:---:|:---:|:---:|
| Xem versions (submitted/fm_confirmed/approved) | ✅ | ✅ | ✅ | ✅ |
| Xem versions (draft) | ✅ | ❌ | ✅ | ❌ |
| Tạo / clone version mới | ✅ | ❌ | ✅ | ❌ |
| Chỉnh sửa (update) | ✅ | ❌ | ✅ | ❌ |
| Xóa version | ✅ | ❌ | ✅ | ❌ |
| Submit | ✅ | ❌ | ✅ | ❌ |
| Gửi feedback (per-section) | ❌ | ❌ | ❌ | ✅ |
| Respond feedback (resolve/reject) | ✅ | ❌ | ✅ | ❌ |
| FM Confirm | ❌ | ❌ | ❌ | ✅ |
| Approve | ✅ | ✅ | ❌ | ❌ |
| Withdraw | ✅ | ❌ | ✅ | ❌ |

### Tạo / chỉnh sửa (Admin & Researcher)

| Action | Endpoint | Điều kiện |
|---|---|---|
| Tạo version trống | `POST /quotation` | Version tăng tự động |
| Clone từ version có sẵn | `POST /quotation/{id}/clone` | Copy toàn bộ data, tạo draft mới |
| Xem chi tiết | `GET /quotation/{versionId}/view` | Tất cả roles |
| Cập nhật data | `PUT /quotation/{versionId}/update` | Chỉ khi status = `draft` |
| Xóa version | `DELETE /quotation/{versionId}/destroy` | Chỉ `draft`; chỉ người tạo; nếu xóa hết → tự tạo lại v1 |

**Validation khi update:** yêu cầu `internal_code`, `project_name`, `platform`, `project_objectives`.  
Đồng thời update luôn `Project.internal_code`, `Project.project_name`, `ProjectDetails.platform/project_objectives`.

---

## Bước 4 — Submit Quotation

**Ai làm:** Người tạo version đó (Researcher)

**Endpoint:** `PUT /api/project-management/projects/{projectId}/quotation/{versionId}/submit`

**Điều kiện phải thỏa:**
1. Status hiện tại phải là `draft`
2. Người gọi API phải là `created_user_id` của version đó
3. Dự án phải có **ít nhất 2 người** trong `projectPermissions` (người tạo + ít nhất 1 người khác)

**Sau khi submit:**
- Status chuyển thành `submitted`
- Ghi lại `submitted_user_id`
- **Gửi Notification** cho tất cả users trong `projectPermissions` với:
  - `type`: `quotation_submitted`
  - `message`: `"Quotation v{N} submitted for {CODE} - {NAME}"`
  - `url`: `/project-management/projects/{id}/quotation`

---

## Bước 5 — Field Manager Review & Feedback

Sau khi quotation `submitted`, Field Manager review từng section, gửi feedback để Researcher chỉnh sửa.

### Thread model

Cột `feedbacks JSON NULL` trên bảng `quotations`. Key = `field.name` của section/repeater trong schema.  
Mỗi key là một **mảng thread entries** (sequential array):

```json
{
  "target_audiences": [
    { "type": "feedback", "content": "Cần thêm nhóm 45-55", "user_id": 5, "user_name": "FM A", "created_at": "..." },
    { "type": "response", "status": "resolved", "content": "Đã thêm", "user_id": 3, "user_name": "Researcher B", "created_at": "..." }
  ]
}
```

| `type` | Ai gửi | Ý nghĩa |
|---|---|---|
| `feedback` | Field Manager | FM gửi yêu cầu chỉnh sửa |
| `response` | Researcher / Admin | Researcher phản hồi, kèm `status: resolved/rejected` |

**Thread status** được xác định bởi entry cuối cùng:
- Entry cuối `type: feedback` → **Pending** (chờ Researcher phản hồi)
- Entry cuối `type: response, status: resolved` → **Resolved**
- Entry cuối `type: response, status: rejected` → **Rejected**

### Endpoints

| Action | Endpoint | Role |
|---|---|---|
| Gửi feedback | `PUT /{versionId}/feedback` | Field Manager |
| Respond (resolve/reject) | `PUT /{versionId}/feedback-response` | Researcher / Admin |

### UI — Feedback icon trên mỗi field

- Icon `<ChatBubbleOutline>` nhỏ bên cạnh label của mỗi `SectionRow` / `RepeaterRow`
- Màu icon theo trạng thái thread:
  - Không có feedback → màu mờ (disabled)
  - Pending → **cam/warning**
  - Resolved → **xanh lá/success**
  - Rejected → **đỏ/error**
- Chip badge hiển thị thẳng trên label (không cần click):
  - `Pending` chip màu warning
  - `Resolved` chip màu success
  - `Rejected` chip màu error + tooltip hiện lý do reject
- Click icon hoặc chip → mở Dialog hiển thị thread + textarea nhập tin mới
- Icon hiển thị cả khi đang ở chế độ edit (isEditing)

### Phân quyền frontend feedback

```
canMutate = user?.role !== 'Field Manager'

onFeedbackSave     = !canMutate  → FM gửi feedback mới
onFeedbackResponse = canMutate   → Researcher/Admin respond (resolve/reject)
```

---

## Bước 6 — FM Confirm

Khi tất cả feedback đã được Researcher xử lý (không còn Pending), Field Manager xác nhận đã review xong.

**Endpoint:** `PUT /api/project-management/projects/{projectId}/quotation/{versionId}/confirm-fm`

**Điều kiện:**
1. Người gọi phải có role `Field Manager`
2. Status hiện tại phải là `submitted`
3. **Không còn feedback nào ở trạng thái Pending** (entry cuối của mọi thread phải là `response`, không phải `feedback`)

**Nếu vi phạm điều kiện 3:** trả về 422 — `"Còn feedback chưa được xử lý. Researcher phải resolve hoặc reject tất cả feedback trước khi FM confirm."`

**Sau khi confirm:**
- Status chuyển thành `fm_confirmed`
- Ghi lại `fm_confirmed_user_id` và `fm_confirmed_at`
- Gửi Notification cho tất cả assigned users (`type: quotation_fm_confirmed`)

**UI:** Nút `CheckCircleIcon` màu info hiển thị cho FM khi status = `submitted`.  
Nút bị **disabled** (kèm tooltip giải thích) khi còn pending feedback.

---

## Bước 7 — Approve

**Ai làm:** `Admin` hoặc `Finance`

**Endpoint:** `PUT /api/project-management/projects/{projectId}/quotation/{versionId}/approve`

**Điều kiện:** Status = `fm_confirmed`; người gọi phải có role `Admin` hoặc `Finance`

**Sau khi approve:** Status → `approved`, ghi `approved_user_id` và `approved_at`.

---

## Bước 8 — Withdraw — Rút lại

> 💡 **Ý nghĩa thực tế:** Đây **không phải** CS/Admin từ chối quotation, mà là Researcher **tự rút lại** để chỉnh sửa và nộp lại.

**Endpoint:** `POST /api/project-management/projects/{projectId}/quotation/{versionId}/withdraw`

**Hành vi theo status hiện tại:**

| Status hiện tại | Sau khi withdraw | Ý nghĩa |
|---|---|---|
| `submitted` | `draft` | Researcher rút về để chỉnh sửa |
| `fm_confirmed` | `submitted` | FM đã confirm nhưng cần review lại — FM confirmation bị xóa |

**Khi rút từ `fm_confirmed`:** `fm_confirmed_user_id` và `fm_confirmed_at` được clear về `null`.

**Gửi Notification** cho tất cả assigned users (`type: quotation_withdrawn`).

---

## State Machine — Quotation Status

```
         tạo mới / clone
              │
              ▼
           [draft] ◄──────────────────── withdraw (submitted → draft)
              │
           submit
     (cần ≥2 users assigned)
              │
              ▼
         [submitted] ◄─────────────────── withdraw (fm_confirmed → submitted)
              │
     FM Confirm                           ← điều kiện: không còn pending feedback
     (Field Manager)
              │
              ▼
       [fm_confirmed]
              │
           approve
       (Admin / Finance)
              │
              ▼
          [approved]
```

---

## Roles & Permissions

| Action | Admin | Finance | Researcher | Field Manager | Scripter |
|---|:---:|:---:|:---:|:---:|:---:|
| Tạo project | ✅ | ❌ | ✅ | ❌ | ❌ |
| Assign users | ✅ | ❌ | ✅ | ✅ | ✅ |
| Xem danh sách dự án | ✅ | ❌ | ✅ | ✅ | ✅ |
| Xem quotation (submitted/fm_confirmed/approved) | ✅ | ✅ | ✅ | ✅ | ❌ |
| Xem quotation (draft) | ✅ | ❌ | ✅ | ❌ | ❌ |
| Tạo / clone / sửa / xóa quotation | ✅ | ❌ | ✅ | ❌ | ❌ |
| Submit quotation | ✅ | ❌ | ✅ | ❌ | ❌ |
| Gửi feedback | ❌ | ❌ | ❌ | ✅ | ❌ |
| Respond feedback (resolve/reject) | ✅ | ❌ | ✅ | ❌ | ❌ |
| FM Confirm | ❌ | ❌ | ❌ | ✅ | ❌ |
| Approve | ✅ | ✅ | ❌ | ❌ | ❌ |
| Withdraw | ✅ | ❌ | ✅ | ❌ | ❌ |

---

## Frontend — Quotation Form (QuotationDynamicForm)

Schema cho form được parse từ `backend/storage/schema/quotation_template.xlsx` qua `TemplateParserService`.

### Điều chỉnh column width
Table dùng `tableLayout: 'fixed'` + `<colgroup>` để cố định cột label:
```tsx
<Table size="small" sx={{ tableLayout: 'fixed' }}>
  <colgroup>
    <col style={{ width: '200px' }} />  {/* cột label */}
    <col />                              {/* cột value — chiếm phần còn lại */}
  </colgroup>
```
> `table-layout: auto` (mặc định của browser) sẽ bỏ qua `colgroup` width — **bắt buộc phải có `tableLayout: 'fixed'`**.

### Dynamic options — `implementation_area.target_audience`
Field `target_audience` trong repeater `implementation_area` **không có options tĩnh** trong Excel.  
Options được sinh động từ dữ liệu đã nhập trong repeater `target_audiences`:

```
target_audiences rows (user thêm)
  → [{ target_audience: "Nam 18-35", ... }, { target_audience: "Nữ 25-40", ... }]
         ↓  useMemo (enrichedSchema)
implementation_area.target_audience.options
  → [{ value: "Nam 18-35", label: "Nam 18-35" }, { value: "Nữ 25-40", label: "Nữ 25-40" }]
```

Logic nằm trong `QuotationDynamicForm.tsx` — `enrichedSchema` useMemo chạy lại mỗi khi `rows['target_audiences']` thay đổi. Không cần sửa backend hay Excel.

### Dependent field clearing (SectionRow)
Các field `category` / `subcategory` có parent-child dependency với `industry` / `category`.  
Khi parent thay đổi, các field con được clear về `undefined` — áp dụng cho **cả `single-select` lẫn `multi-select`**:

| Parent thay đổi | Field bị clear |
|---|---|
| `industry` | `category`, `subcategory` |
| `category` | `subcategory` |

Options của `category`/`subcategory` được filter theo `parent` value. Khi industry/category là multi-select, lấy phần tử đầu tiên (`[0]`) để filter.

---

## Frontend — EstimateCost (Tab "Chi phí dự kiến")

### Template mặc định
File: `frontend/src/pages/Project/Quotation/EstimateCostTemplate.ts`  
Function: `createDefaultEstimateCost()` — trả về cây `EstimateCostNode[]` khớp với cấu trúc Excel.

Các nhóm trong template:
| Group | Mô tả |
|---|---|
| INTERVIEWER | Chi phí phỏng vấn viên (pilot, gửi xe, recruit, CLT, thuê laptop) |
| SUPERVISOR/ASSISTANT | Chi phí quản lý và trợ lý field |
| QC | Chi phí kiểm tra chất lượng |
| DP | Chi phí coding và supervisor DP |
| INCENTIVE | *(items do user tự thêm theo từng dự án)* |

### Hành vi nút "Generate Estimated Cost"
- Nếu `estimateCostItems` **rỗng** → tự động load template mặc định trước khi mở tab
- Nếu đã có data → mở thẳng tab, không ghi đè

### Nút "Load Template" (trong tab EstimateCost)
- Confirm trước khi reset: `"Reset về template mặc định? Dữ liệu hiện tại sẽ bị xóa."`
- Sau khi confirm → gọi `createDefaultEstimateCost()` và replace toàn bộ `estimateCostItems`
- Mỗi lần gọi tạo UUIDs mới (`crypto.randomUUID()`) — tránh key collision

---

## Đã xử lý

- [x] `withdraw()` đã được chuẩn hóa signature thành `($projectId, $versionId)` — nhất quán với các action khác.
- [x] Route đổi thành `POST /{versionId}/withdraw` — rõ nghĩa hơn `reject`.
- [x] Notification được gửi khi withdraw (type: `quotation_withdrawn`).
- [x] Response key đồng nhất: trả về `data` thay vì `quotation`.
- [x] Frontend: thêm `withdrawQuotationVersion()` vào `useQuotation.ts` và `ApiConfig.ts`.
- [x] Field Manager chỉ xem quotation — toàn bộ action buttons ẩn qua `canMutate = user?.role !== 'Field Manager'` trong `Quotation.tsx`.
- [x] `QuotationDynamicForm`: thêm `tableLayout: fixed` + `colgroup` để cố định độ rộng cột label (200px).
- [x] `QuotationDynamicForm`: `enrichedSchema` useMemo — inject dynamic options cho `implementation_area.target_audience` từ `target_audiences` repeater.
- [x] `SectionRow`: fix clearing `category`/`subcategory` khi parent field là `multi-select` (trước đây chỉ clear với `single-select`).
- [x] `SectionRow`: fix filter options khi parent là multi-select — lấy `[0]` và kiểm tra `typeof === 'object'` trước khi dùng `.value`.
- [x] Tạo `EstimateCostTemplate.ts` với `createDefaultEstimateCost()` — 5 nhóm: INTERVIEWER, SUPERVISOR/ASSISTANT, QC, DP, INCENTIVE.
- [x] `Quotation.tsx`: "Generate Estimated Cost" auto-load template khi rỗng; thêm nút "Load Template" để reset về default.
- [x] Per-section feedback thread: migration `feedbacks JSON`, endpoint `PUT /{versionId}/feedback` + `PUT /{versionId}/feedback-response`, thread UI (bubble chat) trên `SectionRow`/`RepeaterRow`.
- [x] Feedback thread status badges: Pending/Resolved/Rejected chip hiển thị ngay trên label, không cần click.
- [x] Fix PHP mixed-array bug: `normalizeThread()` xử lý 3 format (old string-key, new list, mixed) ở cả Controller (write path) và QuotationVersionResource (read path).
- [x] `saveFeedback` / `saveFeedbackResponse` hook chỉ merge `feedbacks` field vào `setSelectedVersion` — tránh reset draft đang chỉnh sửa.
- [x] Thêm status `fm_confirmed`: migration thêm `fm_confirmed_user_id`/`fm_confirmed_at`, endpoint `PUT /{versionId}/confirm-fm`, role check (Field Manager only), pending feedback check.
- [x] `approve()` chuyển sang yêu cầu status `fm_confirmed` thay vì `submitted`; chỉ role `Admin`/`Finance` được approve.
- [x] `withdraw()` hỗ trợ 2 path: `submitted → draft` và `fm_confirmed → submitted` (clear fm_confirmed fields).
- [x] Frontend: chip "FM Confirmed" màu info trong version dropdown; FM Confirm button disabled khi còn pending feedback kèm tooltip giải thích; hiển thị "FM Confirmed by/at" trong info row.
