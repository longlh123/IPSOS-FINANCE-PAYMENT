* Dựng backend + database
** Backend:
1. Tải Herd
2. Copy source backend vào thư mục Herd (C:/Users/<username>/Herd)
3. Chạy lệnh: composer install
4. Chạy "php artisan serve" hoặc "mở app Herd và run API" để test API

** Database:
1. Tải MySQL Installer (bản Community)
2. Tạo database mới và import file dump schema.sql vào
3. Tạo file .env trong folder backend và thêm thông tin connect database vào (theo mẫu .env.example)
4. Thử chạy "php artisan serve" hoặc "mở app Herd để run API" và test đã connect được database chưa bằng cách truy cập vào trang web có URL và port đang run API
5. Khi connect được database, chạy các lệnh sau để :
php artisan migrate:fresh
php artisan db:seed
6. Mở herd và chạy API

** Test (với folder backend được đặt tên là ispos-backend-test-12062024):
1. GET projects: http://ispos-backend-test-12062024.test/api/project-management/projects?platform=ifield&created_user_id=11
2. GET project-types: http://ispos-backend-test-12062024.test/api/project-management/project-types
2. GET provinces: http://ispos-backend-test-12062024.test/api/administrative-divisions/provinces


