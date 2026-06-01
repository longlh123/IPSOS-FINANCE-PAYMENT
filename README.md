# IPSOS Finance Payment

Hệ thống quản lý dự án, báo giá và thanh toán cho IPSOS. Dự án gồm frontend React TypeScript, backend Laravel API và cơ sở dữ liệu MySQL.

## Tính năng chính

- Đăng nhập, đăng xuất, quên mật khẩu và đặt lại mật khẩu.
- Quản lý dự án: tạo dự án, cập nhật thông tin, đổi trạng thái, vô hiệu hóa dự án và phân quyền người dùng theo dự án.
- Quản lý báo giá: tạo phiên bản báo giá, chỉnh sửa, clone, submit, approve và reject.
- Quản lý giao dịch: xem giao dịch, xuất Excel, xử lý giao dịch Vinnet/GotIt và custom voucher.
- Quản lý nhân sự part-time, respondent offline và Mini CATI.
- Quản lý tài khoản người dùng, vai trò và thông báo.
- Dashboard/biểu đồ cho Techcombank Panel.
- Import/export dữ liệu bằng Excel.

## Công nghệ sử dụng

### Frontend

- React 18
- TypeScript
- React Router
- Material UI
- React Query
- Axios
- Recharts, D3, MUI X Charts
- XLSX, File Saver

### Backend

- PHP 8.2+
- Laravel 11
- Laravel Sanctum
- Laravel Breeze
- Laravel Telescope
- Maatwebsite Excel
- MySQL
- QR Code packages

## Cấu trúc thư mục

```text
.
├── backend/            # Laravel API
├── frontend/           # React TypeScript app
├── database/           # SQL schema, backup và query mẫu
├── API/                # Ảnh minh họa API
├── UI_design/          # Ảnh thiết kế giao diện
├── docker-compose.yaml # Chạy frontend, backend và MySQL bằng Docker
└── README.md
```

## Yêu cầu môi trường

- Node.js 18+ hoặc phiên bản tương thích với React Scripts 5
- npm
- PHP 8.2+
- Composer
- MySQL 8
- Docker Desktop nếu muốn chạy bằng Docker

## Cài đặt thủ công

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Cập nhật cấu hình database trong `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_payment
DB_USERNAME=root
DB_PASSWORD=your_password
```

Chạy migration và seed dữ liệu nếu cần:

```bash
php artisan migrate
php artisan db:seed
```

Khởi động backend:

```bash
php artisan serve
```

Backend mặc định chạy tại:

```text
http://localhost:8000
```

### 2. Frontend

```bash
cd frontend
npm install
npm start
```

Frontend mặc định chạy tại:

```text
http://localhost:3000
```

API host đang được cấu hình trong:

```text
frontend/src/config/ApiConfig.ts
```

Giá trị hiện tại:

```ts
const host = "http://localhost:8000"
```

## Chạy bằng Docker

Từ thư mục root của dự án:

```bash
docker compose up --build
```

Các service mặc định:

| Service | URL/Port |
| --- | --- |
| Frontend | `http://localhost:3000` |
| Backend | `http://localhost:8000` |
| MySQL | `localhost:3307` |

## API chính

Base URL:

```text
http://localhost:8000/api
```

Một số endpoint quan trọng:

| Method | Endpoint | Mô tả |
| --- | --- | --- |
| `POST` | `/login` | Đăng nhập |
| `POST` | `/logout` | Đăng xuất |
| `POST` | `/forgot-password` | Gửi email quên mật khẩu |
| `POST` | `/reset-password` | Đặt lại mật khẩu |
| `GET` | `/project-management/metadata` | Lấy metadata cho project |
| `GET` | `/project-management/projects` | Danh sách dự án |
| `POST` | `/project-management/projects/store` | Tạo dự án |
| `PUT` | `/project-management/projects/{projectId}/status` | Cập nhật trạng thái dự án |
| `PUT` | `/project-management/projects/{projectId}/assign-users` | Gán user vào dự án |
| `GET` | `/project-management/projects/{projectId}/quotation/versions` | Danh sách phiên bản báo giá |
| `POST` | `/project-management/projects/{projectId}/quotation` | Tạo báo giá |
| `GET` | `/notifications` | Danh sách thông báo |
| `GET` | `/notifications/unread-count` | Số thông báo chưa đọc |
| `GET` | `/transaction-management/export` | Xuất giao dịch |

Các endpoint trong nhóm quản trị cần gửi token Sanctum:

```http
Authorization: Bearer <token>
Content-Type: application/json
```

## Các route frontend chính

| Route | Màn hình |
| --- | --- |
| `/login` | Đăng nhập |
| `/forgot-password` | Quên mật khẩu |
| `/project-management/projects` | Danh sách dự án |
| `/project-management/projects/:id/quotation` | Quản lý báo giá |
| `/project-management/projects/:id/assignment` | Phân công người dùng |
| `/project-management/projects/:id/parttime-employees` | Nhân sự part-time |
| `/project-management/projects/:id/gifts` | Quà tặng |
| `/project-management/projects/:id/cati-settings` | Cấu hình Mini CATI |
| `/project-management/projects/:id/settings` | Cài đặt dự án |
| `/transaction-manager/transactions` | Giao dịch |
| `/account-management/accounts` | Quản lý tài khoản |
| `/mini-cati/login` | Đăng nhập Mini CATI |
| `/mini-cati` | Màn hình Mini CATI |
| `/custom-voucher/:token` | Gán custom voucher |
| `/search-link` | Tra cứu custom voucher |

## Database

Thư mục `database/` chứa các file SQL hỗ trợ:

- `schema.sql`: schema database.
- `backup_file.sql`: file backup dữ liệu.
- `query.sql`: query mẫu hoặc query hỗ trợ.

Có thể import schema vào MySQL trước khi chạy backend nếu không dùng migration:

```bash
mysql -u root -p finance_payment < database/schema.sql
```

## Lệnh thường dùng

### Backend

```bash
cd backend
php artisan serve
php artisan migrate
php artisan db:seed
php artisan test
```

### Frontend

```bash
cd frontend
npm start
npm run build
npm test
```

## Ghi chú phát triển

- Không commit file `.env`, private key hoặc thông tin bí mật.
- Nếu thay đổi host API, cập nhật `frontend/src/config/ApiConfig.ts`.
- Một số API yêu cầu role phù hợp như `Admin`, `Scripter`, `Reseacher`, `Field Manager` hoặc `Finance`.
- Worktree hiện có cả tài liệu API/UI trong thư mục `API/` và `UI_design/` để tham khảo thêm khi phát triển giao diện.
