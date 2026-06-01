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
  Researcher/Admin approve
  hoặc Researcher rút lại (withdraw)
        │                   │
        ▼                   ▼
    approved            [draft] → chỉnh sửa → submit lại
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

> - **Researcher**: thấy tất cả status (`draft`, `submitted`, `approved`)
> - **Admin**: thấy `submitted` và `approved`
> - **Field Manager**: thấy `submitted` và `approved` — **chỉ xem, không thao tác**

### Phân quyền theo role

| Action | Admin | Researcher | Field Manager |
|---|:---:|:---:|:---:|
| Xem versions (submitted/approved) | ✅ | ✅ | ✅ |
| Xem versions (draft) | ❌ | ✅ | ❌ |
| Tạo / clone version mới | ✅ | ✅ | ❌ |
| Chỉnh sửa (update) | ✅ | ✅ | ❌ |
| Xóa version | ✅ | ✅ | ❌ |
| Submit | ✅ | ✅ | ❌ |
| Approve | ✅ | ✅ | ❌ |
| Withdraw (rút lại) | ✅ | ✅ | ❌ |

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

## Bước 5 — Approve hoặc Withdraw (Rút lại)

### Approve

**Endpoint:** `PUT /api/project-management/projects/{projectId}/quotation/{versionId}/approve`

**Điều kiện:** Status = `submitted`; người gọi phải là `created_user_id`

> 📝 **Note (cần confirm với team):** Hiện tại chỉ người tạo quotation mới approve được. Cần thảo luận xem có nên cho CS/Admin approve thay không.

**Sau khi approve:** Status → `approved`, ghi `approved_user_id` và `approved_at`.

### Withdraw — Rút lại submission

**Endpoint:** `POST /api/project-management/projects/{projectId}/quotation/{versionId}/withdraw`

> 💡 **Ý nghĩa thực tế:** Đây **không phải** CS/Admin từ chối quotation, mà là Researcher **tự rút lại** bản đã submit để chỉnh sửa và nộp lại.

**Điều kiện:** Status = `submitted` (không check người gọi — ai trong dự án cũng có thể rút)

**Sau khi withdraw:** Status → `draft` → Researcher edit lại và submit lại ngay, không qua bước trung gian.

---

## State Machine — Quotation Status

```
         tạo mới / clone
              │
              ▼
           [draft] ◄─── withdraw (tự rút lại)
              │                    │
           submit                  │
     (cần ≥2 users assigned)       │
              │                    │
              ▼                    │
         [submitted] ──────────────┘
              │
           approve
              │
              ▼
          [approved]
```

---

## Roles & Permissions

| Action | Admin | Researcher | Field Manager | Scripter | Finance |
|---|:---:|:---:|:---:|:---:|:---:|
| Tạo project | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assign users | ✅ | ✅ | ✅ | ✅ | ❌ |
| Xem danh sách dự án | ✅ | ✅ | ✅ (chỉ dự án có quotation submitted/approved) | ✅ | ❌ |
| Xem quotation (submitted/approved) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Xem quotation (draft) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Tạo / clone / sửa / xóa quotation | ✅ | ✅ | ❌ | ❌ | ❌ |
| Submit quotation | ✅ | ✅ | ❌ | ❌ | ❌ |
| Approve quotation | ✅ | ✅ | ❌ | ❌ | ❌ |
| Withdraw (rút lại) | ✅ | ✅ | ❌ | ❌ | ❌ |

---

## Điểm cần chú ý / TODO

- [ ] **[Cần thảo luận với team]** `approve()` hiện chỉ cho phép `created_user_id` approve quotation của chính họ. Xem xét có cần mở thêm cho CS/Admin không.

## Đã xử lý

- [x] `withdraw()` đã được chuẩn hóa signature thành `($projectId, $versionId)` — nhất quán với các action khác.
- [x] Route đổi thành `POST /{versionId}/withdraw` — rõ nghĩa hơn `reject`.
- [x] Notification được gửi khi withdraw (type: `quotation_withdrawn`).
- [x] Response key đồng nhất: trả về `data` thay vì `quotation`.
- [x] Frontend: thêm `withdrawQuotationVersion()` vào `useQuotation.ts` và `ApiConfig.ts`.
- [x] Field Manager chỉ xem quotation — toàn bộ action buttons ẩn qua `canMutate = user?.role !== 'Field Manager'` trong `Quotation.tsx`.
