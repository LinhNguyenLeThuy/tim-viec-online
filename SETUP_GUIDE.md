# TÀI LIỆU HƯỚNG DẪN SETUP DỰ ÁN & PHÂN CÔNG THÀNH VIÊN (SETUP_GUIDE.MD)
> **Dự án:** Web Tìm Việc Làm Online (`tim-viec-online`)  
> **Kiến trúc:** Monorepo (`/backend` và `/frontend`)  
> **Cập nhật:** Học kỳ đồ án tốt nghiệp / Bài tập lớn

---

## MỤC LỤC
1. [Yêu cầu Môi trường & Công cụ Cài đặt](#1-yêu-cầu-môi-trường--công-cụ-cài-đặt)
2. [Hướng dẫn Clone & Quy tắc Phân nhánh Git](#2-hướng-dẫn-clone--quy-tắc-phân-nhánh-git)
3. [Cài đặt Cơ sở Dữ liệu (MySQL / phpMyAdmin)](#3-cài-đặt-cơ-sở-dữ-liệu-mysql--phpmyadmin)
4. [Hướng dẫn Khởi chạy Backend (Laravel 11)](#4-hướng-dẫn-khởi-chạy-backend-laravel-11)
5. [Hướng dẫn Khởi chạy Frontend (Next.js 14)](#5-hướng-dẫn-khởi-chạy-frontend-nextjs-14)
6. [Hướng dẫn Chi tiết & Checklist Từng Thành viên](#6-hướng-dẫn-chi-tiết--checklist-từng-thành-viên)
   - [Member 1: Backend Lead (Auth, System & AI CV Analysis)](#member-1--backend-lead-auth-system--ai-cv-analysis)
   - [Member 2: Backend Core (Job Core, Application & Realtime Chat)](#member-2--backend-core-job-core-application--realtime-chat)
   - [Member 3: Frontend (Candidate UI, Job Flow & AI Results)](#member-3--frontend-candidate-ui-job-flow--ai-results)
   - [Member 4: Frontend (Auth, Employer Portal & Realtime Chatbox)](#member-4--frontend-auth-employer-portal--realtime-chatbox)
7. [Quy chuẩn Phối hợp & Tiêu chuẩn API](#7-quy-chuẩn-phối-hợp--tiêu-chuẩn-api)
8. [Xử lý Các Lỗi Thường Gặp (Troubleshooting)](#8-xử-lý-các-lỗi-thường-gặp-troubleshooting)

---

## 1. Yêu cầu Môi trường & Công cụ Cài đặt

Trước khi bắt đầu, mọi thành viên trong nhóm bắt buộc phải cài đặt các công cụ sau trên máy cá nhân:

| Công cụ | Phiên bản yêu cầu | Mục đích | Cách kiểm tra trên Terminal |
|---|---|---|---|
| **Git** | >= 2.30 | Quản lý mã nguồn | `git --version` |
| **PHP** | >= 8.2 (Khuyên dùng PHP 8.2 hoặc 8.3) | Chạy Laravel 11 | `php -v` |
| **Composer** | >= 2.5 | Quản lý gói thư viện PHP | `composer -v` |
| **Node.js** | >= 18.17 (LTS khuyên dùng: v20.x) | Chạy Next.js | `node -v` |
| **NPM** | >= 9.x | Quản lý package Node | `npm -v` |
| **MySQL** | >= 8.0 (hoặc MariaDB >= 10.4) | Cơ sở dữ liệu | Đi kèm trong Laragon hoặc XAMPP |
| **Môi trường Web Server** | **Laragon** (Khuyên dùng) hoặc **XAMPP** | Chạy Apache, MySQL, phpMyAdmin | Bật Service MySQL & Apache |

> [!TIP]
> Khuyên khích dùng **Laragon** trên Windows vì tốc độ nhanh hơn XAMPP, đã tích hợp sẵn PHP 8.2+, Composer, Git và hỗ trợ tạo Virtual Host tự động.

---

## 2. Hướng dẫn Clone & Quy tắc Phân nhánh Git

### 2.1. Clone dự án về máy
Mở Terminal / PowerShell và chạy:
```bash
git clone https://github.com/ngocbinh15/tim-viec-online.git
cd tim-viec-online
```

### 2.2. Quy tắc Phân nhánh (Branching Strategy)
- **Tuyệt đối KHÔNG commit và push trực tiếp lên nhánh `main`**.
- Nhánh `main` chỉ dùng để merge phiên bản ổn định cuối mỗi tuần để nghiệm thu.
- Nhánh phát triển chính của nhóm là `develop`.
- Mỗi thành viên khi làm tính năng mới phải tạo nhánh riêng từ `develop`:
  ```bash
  # Chuyển sang develop và cập nhật mới nhất
  git checkout develop
  git pull origin develop

  # Tạo nhánh tính năng mới theo quy ước
  git checkout -b feature/<tên-tính-năng>-<tên-member>
  ```
  *Ví dụ:*
  - Member 1: `git checkout -b feature/auth-sanctum-m1`
  - Member 2: `git checkout -b feature/job-crud-api-m2`
  - Member 3: `git checkout -b feature/candidate-ui-m3`
  - Member 4: `git checkout -b feature/chat-websocket-m4`

### 2.3. Quy trình Commit & Tạo Pull Request (PR)
1. Thường xuyên commit các thay đổi có ý nghĩa:
   ```bash
   git add .
   git commit -m "feat(auth): hoàn thành API đăng ký và đăng nhập Sanctum"
   git push origin feature/<tên-nhánh>
   ```
2. Vào GitHub tạo **Pull Request** trỏ vào nhánh `develop`.
3. Báo nhóm qua Zalo/Discord để ít nhất 1 thành viên review trước khi merge.

---

## 3. Cài đặt Cơ sở Dữ liệu (MySQL / phpMyAdmin)

Hệ thống đã có sẵn file lược đồ cơ sở dữ liệu hoàn chỉnh tại thư mục [`db/tim_viec_online.sql`](file:///f:/Soure_Code/tim-viec-online/db/tim_viec_online.sql).

### Cách 1: Import trực tiếp qua giao diện phpMyAdmin
1. Khởi động **Laragon** hoặc **XAMPP**, bấm **Start All** (chạy MySQL).
2. Truy cập trình duyệt: `http://localhost/phpmyadmin`
3. Tạo cơ sở dữ liệu mới:
   - Tên database: `tim_viec_online`
   - Bảng mã (Collation): `utf8mb4_unicode_ci`
4. Chọn database `tim_viec_online` vừa tạo, bấm tab **Import (Nhập)**.
5. Chọn file `db/tim_viec_online.sql` trong thư mục dự án và bấm **Import (Thực hiện)**.
6. Kiểm tra thấy xuất hiện đủ 15 bảng và dữ liệu mẫu test ban đầu.

### Cách 2: Import bằng dòng lệnh Command Line
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS tim_viec_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p tim_viec_online < db/tim_viec_online.sql
```
*(Nếu dùng XAMPP/Laragon mặc định không có mật khẩu, chỉ cần bấm Enter khi được hỏi password).*

---

## 4. Hướng dẫn Khởi chạy Backend (Laravel 11)

Toàn bộ mã nguồn API nằm trong thư mục `/backend`.

### Bước 1: Di chuyển vào thư mục backend & Cài đặt thư viện
```bash
cd backend
composer install
```

### Bước 2: Thiết lập file môi trường `.env`
Sao chép từ file mẫu:
```bash
cp .env.example .env
```
*(Nếu trên PowerShell Windows, dùng lệnh: `copy .env.example .env`)*

### Bước 3: Tạo Application Key
```bash
php artisan key:generate
```

### Bước 4: Cấu hình thông số trong file `.env`
Mở file `.env` và chỉnh sửa các thông số sau:
```ini
APP_NAME="TimViecOnline"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Cấu hình Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tim_viec_online
DB_USERNAME=root
DB_PASSWORD=

# Cấu hình File Storage (Lưu trữ CV PDF và Avatar)
FILESYSTEM_DISK=public

# Cấu hình Realtime WebSocket (Reverb hoặc Pusher)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=timviec_app
REVERB_APP_KEY=timviec_key
REVERB_APP_SECRET=timviec_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Khóa AI Google Gemini (Dành cho Member 1 tích hợp)
GEMINI_API_KEY=your_gemini_api_key_here
```

### Bước 5: Tạo liên kết Symbolic Link cho thư mục lưu trữ file
Bắt buộc chạy lệnh này để xem được file CV và Avatar qua URL công khai:
```bash
php artisan storage:link
```

### Bước 6: Khởi chạy Server Backend
```bash
php artisan serve --port=8000
```
API Backend sẽ chạy tại địa chỉ: **`http://localhost:8000`**.  
Kiểm tra sức khỏe hệ thống: truy cập trình duyệt `http://localhost:8000/api` hoặc endpoint bất kỳ để xác nhận backend hoạt động.

---

## 5. Hướng dẫn Khởi chạy Frontend (Next.js 14)

Mã nguồn giao diện người dùng nằm trong thư mục `/frontend`.

### Bước 1: Mở terminal mới, di chuyển vào thư mục frontend & Cài đặt dependencies
```bash
cd frontend
npm install
```

### Bước 2: Thiết lập file môi trường `.env.local`
Sao chép file cấu hình:
```bash
cp .env.example .env.local
```
*(Trên PowerShell Windows: `copy .env.example .env.local`)*

Mở file `.env.local` và kiểm tra các cấu hình:
```ini
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_STORAGE_URL=http://localhost:8000/storage

# Cấu hình kết nối WebSocket phía Client
NEXT_PUBLIC_REVERB_APP_KEY=timviec_key
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=http
```

### Bước 3: Khởi chạy Frontend Dev Server
```bash
npm run dev
```
Mở trình duyệt truy cập: **`http://localhost:3000`**.

---

## 6. Hướng dẫn Chi tiết & Checklist Từng Thành viên

---

### Member 1 — Backend Lead (Auth, System & AI CV Analysis)

* **Vai trò:** Trưởng nhóm kỹ thuật Backend, phụ trách xác thực, bảo mật, hồ sơ người dùng và lõi AI.
* **Thư mục làm việc chính:** `/backend/app/`, `/backend/routes/api.php`, `/backend/app/Services/`.

#### Công việc cụ thể theo tuần:
* **Tuần 1 (Auth & Base System):**
  - Khởi tạo project Laravel 11 API, cấu hình CORS (`config/cors.php`) cho phép `http://localhost:3000`.
  - Cài đặt & cấu hình **Laravel Sanctum**.
  - Viết các FormRequest validate dữ liệu đăng ký/đăng nhập (Họ tên 2-100 ký tự, mật khẩu mạnh 8-32 ký tự, email chuẩn RFC).
  - Viết Controller & Service cho Authentication:
    - `POST /api/register`: Tự động tạo bản ghi `candidate_profiles` hoặc `companies` tương ứng.
    - `POST /api/login`: Kiểm tra mật khẩu (Bcrypt), xử lý đếm số lần sai `failed_attempts` (khóa sau 5 lần), cấp phát Sanctum Token.
    - `POST /api/logout`: Hủy token hiện tại.
    - `GET /api/user`: Lấy thông tin user đăng nhập.
* **Tuần 2 (Profile & Gemini AI Service):**
  - Viết API cập nhật hồ sơ cá nhân:
    - `PUT /api/candidate/profile`: Upload CV PDF (<= 5MB, validate mime `application/pdf`), lưu vào `storage/app/public/resumes/`.
    - `PUT /api/employer/company`: Cập nhật thông tin công ty, logo, website, quy mô.
  - Tích hợp **Google Gemini 1.5 Flash API**:
    - Tạo `app/Services/GeminiService.php`.
    - Truyền nội dung CV PDF và JD công việc vào Gemini Prompt với định dạng trả về **Structured JSON Output**:
      ```json
      {
        "match_score": 85,
        "summary": "Ứng viên có kỹ năng backend tốt, phù hợp cao với vị trí.",
        "matched_skills": ["PHP", "Laravel", "MySQL"],
        "missing_skills": ["Docker", "Redis"],
        "strengths": ["Đúng khung công nghệ", "2 năm kinh nghiệm thực chiến"],
        "recommendations": ["Cần bổ sung kiến thức về Caching và containerization"]
      }
      ```
    - Lưu kết quả vào bảng `ai_cv_analyses` (lưu vĩnh viễn, không tính toán lại).
* **Tuần 3 (Admin API & Nghiệm thu):**
  - Viết API cho Admin:
    - `GET /api/admin/stats`: Thống kê tổng số Ứng viên, NTD, Jobs, Lượt nộp.
    - `GET /api/admin/users`: Danh sách người dùng, tìm kiếm & lọc theo role.
    - `PATCH /api/admin/users/{id}/status`: Khóa (`locked`) hoặc mở khóa (`active`).
  - Tối ưu hiệu năng, kiểm tra bảo mật (SQL Injection, XSS, MIME Sniffing).

#### Checklist hoàn thành của Member 1:
- [ ] Đăng ký / Đăng nhập hoạt động mượt mà, trả token chuẩn Sanctum.
- [ ] Upload CV file PDF thành công, có link xem trực tiếp trên browser.
- [ ] Gọi Google Gemini API thành công, trả về điểm số Match Score và mảng kỹ năng đúng format JSON.
- [ ] Admin khóa tài khoản thành công, tài khoản bị khóa không đăng nhập được.

---

### Member 2 — Backend Core (Job Core, Application & Realtime Chat)

* **Vai trò:** Phụ trách toàn bộ nghiệp vụ cốt lõi về Việc làm, Nộp hồ sơ và Hạ tầng Tin nhắn Thời gian thực.
* **Thư mục làm việc chính:** `/backend/app/Http/Controllers/`, `/backend/app/Events/`, `/backend/routes/api.php`.

#### Công việc cụ thể theo tuần:
* **Tuần 1 (Danh mục & Master Data):**
  - Viết Controller & Service quản lý Danh mục & Kỹ năng:
    - `GET /api/categories`, `GET /api/skills` (Public).
    - `POST / PUT / DELETE` cho Admin quản lý danh mục.
  - Viết Database Seeder cho 5 ngành nghề và 10+ kỹ năng phổ biến.
* **Tuần 2 (Job Core & Nộp hồ sơ):**
  - Viết API Quản lý Tin tuyển dụng:
    - `GET /api/jobs`: Tìm kiếm & Lọc đa tiêu chí (keyword, category_id, city, khoảng lương, job_type). Sắp xếp theo thời gian đẩy bài gần nhất (`ORDER BY updated_at DESC` theo cơ chế bump tin FR-05.4). Phân trang (Pagination 10 jobs/trang).
    - `GET /api/jobs/{id}`: Chi tiết việc làm (tăng `views_count`).
    - `POST /api/employer/jobs`: NTD đăng tin mới (validate deadline > today, lương min <= lương max, status mặc định `active`).
    - `PATCH /api/employer/jobs/{id}/status`: Đóng/Mở lại tin tuyển dụng (`active` / `closed`).
  - Viết API Nộp đơn ứng tuyển (Application Flow):
    - `POST /api/jobs/{id}/apply`: Ứng viên nộp hồ sơ. Ràng buộc: Mỗi ứng viên chỉ được nộp **1 lần** vào 1 job đang mở. Sau khi lưu thành công, tự động cập nhật thời gian tin (`$job->touch()` - cơ chế bump_at theo FR-05.4 để đẩy tin lên đầu) và kích hoạt Event/Service gọi AI phân tích của Member 1.
    - `DELETE /api/candidate/applications/{id}/withdraw`: Rút hồ sơ (chỉ cho phép khi hồ sơ còn ở trạng thái `applied`).
    - `GET /api/employer/jobs/{id}/applications`: NTD xem danh sách đơn nộp kèm điểm số AI.
    - `PATCH /api/employer/applications/{id}/status`: Đổi trạng thái (`waiting`, `accepted`, `rejected`).
* **Tuần 3 (Realtime Chat & WebSocket):**
  - Setup **Laravel Reverb** (hoặc Pusher Channels).
  - Viết API Chat 1-1 giữa NTD và Ứng viên đã nộp đơn:
    - `GET /api/conversations`: Danh sách phòng chat kèm tin nhắn mới nhất.
    - `GET /api/conversations/{id}/messages`: Lịch sử chat (phân trang).
    - `POST /api/conversations/{id}/messages`: Gửi tin nhắn mới.
  - Tạo Broadcast Event `MessageSent implements ShouldBroadcast` để bắn dữ liệu qua WebSocket tức thì dưới 1 giây.
  - Viết API Thông báo In-App (`/api/notifications`).

#### Checklist hoàn thành của Member 2:
- [ ] API lọc việc làm theo đa tiêu chí (ngành, tỉnh thành, khoảng lương) trả kết quả chính xác.
- [ ] Nộp đơn ứng tuyển chặn được tình trạng nộp trùng lặp 2 lần.
- [ ] NTD đổi trạng thái đơn nộp thành công và cập nhật `reviewed_at` khi mở CV.
- [ ] Broadcast Event `MessageSent` hoạt động, tin nhắn bắn qua Reverb/Pusher thành công.

---

### Member 3 — Frontend (Candidate UI, Job Flow & AI Results)

* **Vai trò:** Xây dựng giao diện công khai và trải nghiệm người tìm việc (Candidate Portal) với Next.js 14 App Router và Tailwind CSS.
* **Thư mục làm việc chính:** `/frontend/src/app/(public)/`, `/frontend/src/app/(candidate)/`, `/frontend/src/components/`.

#### Công việc cụ thể theo tuần:
* **Tuần 1 (Khung giao diện & Layout):**
  - Thiết lập Next.js 14 App Router, cài đặt `shadcn/ui`, `lucide-react`, `axios`, `sonner`.
  - Thiết lập Design System: Màu sắc chuyên nghiệp (Slate/Zinc, Emerald/Indigo nhấn), typography Font Inter.
  - Dựng Layout dùng chung:
    - `Header`: Logo, menu điều hướng, thanh tìm kiếm nhanh, nút Đăng nhập/Đăng ký / User Dropdown.
    - `Footer`: Thông tin sàn việc làm, liên kết chính sách, bản quyền.
* **Tuần 2 (Trang chủ, Tìm kiếm & Chi tiết Job):**
  - **Trang chủ (`/`):**
    - Hero banner tìm kiếm lớn (nhập từ khóa, chọn địa điểm).
    - Danh mục ngành nghề nổi bật (icon sinh động).
    - Danh sách việc làm mới nhất (Job card hiển thị mức lương badge xanh, địa điểm, hạn nộp).
    - Danh sách công ty tuyển dụng hàng đầu.
  - **Trang Tìm kiếm & Lọc việc làm (`/jobs`):**
    - Cột trái: Bộ lọc đa tiêu chí (Ngành nghề checkbox, Tỉnh thành dropdown, mức lương slider/radio, hình thức làm việc).
    - Cột phải: Danh sách tin tuyển dụng, phân trang, trạng thái Empty State khi không có kết quả.
  - **Trang Chi tiết việc làm (`/jobs/[id]`):**
    - Nội dung JD chi tiết, thông tin công ty, nút "Ứng tuyển ngay" và "Lưu tin".
    - **Modal Nộp hồ sơ:** Cho phép chọn "Dùng CV mặc định" hoặc "Tải file CV mới" (kèm thư giới thiệu).
* **Tuần 3 (Kết quả AI Match Score & Lịch sử nộp đơn):**
  - **Widget AI CV Analysis:**
    - Hiển thị điểm số Match Score trực quan (vòng tròn tiến trình màu sắc: Xanh lá >= 80%, Xanh dương 60-79%, Đỏ < 60%).
    - Danh sách Badge kỹ năng đáp ứng (Xanh) và Kỹ năng còn thiếu (Cam/Đỏ).
    - Đoạn đánh giá tổng quan của AI.
  - **Trang Hồ sơ ứng viên (`/candidate/profile`):** Cập nhật thông tin, chọn tối đa 10 kỹ năng, upload CV cá nhân mặc định (kèm PDF Preview).
  - **Trang Lịch sử ứng tuyển (`/candidate/applications`):** Danh sách việc đã nộp kèm trạng thái (Đã nộp, Đang xem xét, Trúng tuyển, Từ chối), nút Rút đơn hồ sơ.

#### Checklist hoàn thành của Member 3:
- [ ] Giao diện responsive tốt trên màn hình máy tính và điện thoại di động (tối thiểu 360px).
- [ ] Bộ lọc việc làm hoạt động mượt mà, phản hồi dưới 1.5 giây.
- [ ] Modal ứng tuyển gửi dữ liệu kèm file PDF lên API thành công.
- [ ] Widget AI hiển thị kết quả phân tích trực quan, không bị "mùi AI", hiệu ứng Skeleton loading khi chờ kết quả.

---

### Member 4 — Frontend (Auth, Employer Portal & Realtime Chatbox)

* **Vai trò:** Phụ trách xác thực người dùng, Cổng quản lý tuyển dụng cho Doanh nghiệp (Employer Portal), Giao diện Admin và Chatbox Realtime qua WebSocket.
* **Thư mục làm việc chính:** `/frontend/src/app/(auth)/`, `/frontend/src/app/(employer)/`, `/frontend/src/app/(admin)/`, `/frontend/src/components/chat/`.

#### Công việc cụ thể theo tuần:
* **Tuần 1 (Auth UI & State Management):**
  - Dựng giao diện trang Đăng nhập (`/login`) và Đăng ký (`/register`):
    - Cho phép chọn vai trò rõ ràng: **Ứng viên** hoặc **Nhà tuyển dụng**.
    - Form validate trực quan bằng `react-hook-form` + `zod` (báo lỗi tiếng Việt).
  - Quản lý trạng thái xác thực (Auth Context / Zustand / LocalStorage):
    - Tự động gắn Token vào header Axios: `Authorization: Bearer <token>`.
    - Bảo vệ route (Middleware Protected Routes): Ứng viên không được vào trang Employer/Admin, Khách bấm nộp đơn tự chuyển về trang Login.
* **Tuần 2 (Employer Portal):**
  - **Trang Cập nhật thông tin công ty (`/employer/company`):** Logo, tên, website, địa chỉ, quy mô, giới thiệu.
  - **Trang Đăng tin tuyển dụng mới (`/employer/jobs/create`):**
    - Form đăng tin chuyên nghiệp: Tiêu đề, ngành nghề, cấp bậc, số lượng tuyển, mức lương (dropdown: khoảng cụ thể, từ X triệu, thỏa thuận), hạn chót nộp hồ sơ, mô tả JD.
  - **Trang Quản lý tin tuyển dụng (`/employer/jobs`):**
    - Bảng danh sách tin đã đăng, số lượng hồ sơ nộp, nút Đóng/Mở tin.
  - **Trang Quản lý ứng viên theo Job (`/employer/jobs/[id]/applicants`):**
    - Danh sách ứng viên nộp hồ sơ, xem điểm AI Match Score để lọc nhanh ứng viên tiềm năng.
    - Xem trực tiếp CV PDF (Modal PDF Viewer).
    - Nút cập nhật trạng thái hồ sơ (Chấp nhận, Từ chối).
* **Tuần 3 (Realtime Chatbox & Admin Dashboard):**
  - **Tích hợp WebSocket (Laravel Echo + Pusher-js):**
    - Kết nối tới Laravel Reverb (`ws://localhost:8080`).
    - Lắng nghe kênh riêng tư `private-chat.{conversationId}` sự kiện `MessageSent`.
  - **Giao diện Chatbox 1-1 Realtime (`/messages`):**
    - Cột trái: Danh sách cuộc hội thoại, hiển thị tin nhắn mới nhất và badge chưa đọc.
    - Cột phải: Cửa sổ chat, tin nhắn gửi/nhận tức thì không cần F5, hiển thị thời gian `HH:mm` và avatar hai bên.
  - **Trang Admin tinh gọn (`/admin`):**
    - Dashboard thống kê 4 thẻ số liệu tổng quan.
    - Bảng quản lý người dùng: Tìm kiếm, nút Khóa/Mở khóa tài khoản.
    - Bảng kiểm tra tin tuyển dụng bị báo cáo (Report).

#### Checklist hoàn thành của Member 4:
- [ ] Đăng ký / Đăng nhập hoạt động chuẩn, chuyển hướng đúng role sau khi đăng nhập.
- [ ] Nhà tuyển dụng tạo và quản lý tin tuyển dụng thành công.
- [ ] Cửa sổ chat nhận tin nhắn thời gian thực qua WebSocket dưới 1 giây.
- [ ] Giao diện Admin xem được thống kê và thao tác khóa tài khoản hoạt động chuẩn xác.

---

## 7. Quy chuẩn Phối hợp & Tiêu chuẩn API

### 7.1. Định dạng JSON Response thống nhất giữa BE và FE
Mọi API trả về bắt buộc tuân thủ đúng định dạng chuẩn:

* **Khi thành công (HTTP 200, 201):**
```json
{
  "success": true,
  "message": "Nộp hồ sơ ứng tuyển thành công!",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "total": 50
  }
}
```

* **Khi thất bại / Lỗi Validate (HTTP 400, 401, 403, 422, 500):**
```json
{
  "success": false,
  "message": "Dữ liệu nhập vào không hợp lệ",
  "errors": {
    "email": ["Email này đã được sử dụng"],
    "password": ["Mật khẩu phải chứa ít nhất 8 ký tự"]
  }
}
```

### 7.2. Quy tắc Họp nhanh (Daily Standup)
Mỗi tối lúc **21:30 - 22:00**, tất cả thành viên dành 10 phút nhắn tin vào nhóm:
1. **Hôm nay tôi đã làm được gì?** (Gửi kèm link PR hoặc ảnh chụp màn hình demo).
2. **Ngày mai tôi sẽ làm gì?**
3. **Có gặp lỗi hoặc vướng mắc nào cần nhóm hỗ trợ không?**

---

## 8. Xử lý Các Lỗi Thường Gặp (Troubleshooting)

### Lỗi 1: Bị chặn CORS khi gọi từ Frontend sang Backend
* **Triệu chứng:** Console trình duyệt báo đỏ: `Access to XMLHttpRequest blocked by CORS policy`.
* **Cách sửa:**
  1. Mở file `backend/config/cors.php`.
  2. Đảm bảo cấu hình:
     ```php
     'paths' => ['api/*', 'sanctum/csrf-cookie'],
     'allowed_origins' => ['http://localhost:3000'],
     'allowed_methods' => ['*'],
     'allowed_headers' => ['*'],
     'supports_credentials' => true,
     ```
  3. Chạy lệnh: `php artisan config:clear`.

### Lỗi 2: Không xem được ảnh hoặc file CV PDF (Lỗi 404 Not Found)
* **Triệu chứng:** Link ảnh hoặc CV trả về `http://localhost:8000/storage/...` nhưng báo 404.
* **Cách sửa:**
  1. Trong thư mục `backend`, chạy lệnh:
     ```bash
     php artisan storage:link
     ```
  2. Nếu bị lỗi link đã tồn tại, xóa thư mục `backend/public/storage` rồi chạy lại lệnh trên.

### Lỗi 3: Không kết nối được WebSocket Reverb
* **Triệu chứng:** Console báo lỗi `WebSocket connection to 'ws://localhost:8080/app/timviec_key' failed`.
* **Cách sửa:**
  1. Đảm bảo đã chạy lệnh khởi động server Reverb trên 1 terminal riêng ở backend:
     ```bash
     php artisan reverb:start --debug
     ```
  2. Kiểm tra port `8080` không bị ứng dụng khác chiếm dụng.
  3. Đảm bảo biến `REVERB_APP_KEY` ở backend `.env` trùng khớp với `NEXT_PUBLIC_REVERB_APP_KEY` ở frontend `.env.local`.

### Lỗi 4: Xung đột Port khi chạy server
* **Port 8000 bị chiếm:** Chạy backend với port khác: `php artisan serve --port=8001` (đồng thời sửa `NEXT_PUBLIC_API_URL` ở frontend).
* **Port 3000 bị chiếm:** Next.js sẽ tự động hỏi chuyển sang port `3001` hoặc chạy: `npm run dev -- -p 3001`.

---
*Chúc cả nhóm phối hợp hiệu quả và hoàn thành xuất sắc đồ án!*
