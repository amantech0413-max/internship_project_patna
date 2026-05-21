# M/s Bhagya Laxmi Internship Management System

**Organization:** M/s Bhagya Laxmi — Mohali, Chandigarh

Monorepo with **Laravel 12 REST API** + **Vue 3 admin panel** (Vite, Bootstrap 5) in one repository, designed for **Flutter** mobile app integration.

## Project Structure

```
internship_program/
├── app/                    # Laravel application (API, services, jobs, observers)
├── database/               # Migrations & seeders
├── routes/api.php          # API v1 routes
├── resources/js/admin/     # Vue 3 admin SPA
├── resources/views/admin.blade.php
├── docs/                   # Postman collection
└── README.md
```

## Features

- Sanctum token authentication
- Role-based access: Super Admin, Admin, Student, College Coordinator
- Student registration (mobile **not** unique — sibling/parent mobile support)
- Login via **registration number**, **student code**, or **email** + password
- **Admin-controlled WhatsApp groups** — no auto group creation; Cloud API for invitation messages only
- Internship groups with manual invite links (Flutter opens WhatsApp to join)
- Admin dashboard, approvals, exports
- Daily reports, assignments, attendance (QR-ready)
- Queued jobs: certificates, offer letters, push notifications
- Mail & database notifications with observers
- Repository + service pattern

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+ (or SQLite for local demo)

## Quick Start

### 1. Backend

```bash
cd internship_program
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

### 2. Queue worker (WhatsApp messages, certificates, notifications)

```bash
php artisan queue:work
```

### WhatsApp Cloud API (Meta)

Admin workflow:
1. Create WhatsApp group manually in WhatsApp app
2. Paste invite link in Admin Panel → Internship Group
3. Assign students → **Send WhatsApp Invitation**
4. Students receive message and join manually via link

Configure in `.env`:

```env
WHATSAPP_ENABLED=true
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_permanent_token
```

API endpoints:
- `POST /api/v1/admin/groups/{id}/whatsapp/send` — bulk invitation
- `GET /api/v1/admin/whatsapp/messages` — message logs
- `POST /api/v1/admin/whatsapp/messages/retry-failed` — retry failed

### 3. Admin frontend (Vue 3 + Vite)

Admin UI source: `resources/js/admin/`. Served at **`/admin`** via Laravel (`resources/views/admin.blade.php`).

**Development** (two terminals, project root):

```bash
npm install
php artisan serve          # terminal 1 — http://127.0.0.1:8000
npm run dev                # terminal 2 — Vite HMR
```

Open: http://127.0.0.1:8000/admin/login  
Public registration: http://127.0.0.1:8000/admin/register

**Production build** (CI commits `public/build`; server only `git pull`):

```bash
npm run build
```

Build output: `public/build/` (committed by CI on deploy).

### Default accounts (after seed)

| Role        | Login                         | Password  |
|-------------|-------------------------------|-----------|
| Super Admin | superadmin@bhagyalaxmi.local  | password  |
| Admin       | admin@bhagyalaxmi.local       | password  |
| Staff       | staff@bhagyalaxmi.local       | password  |

> Students register publicly (no login). Staff use `/admin/login`.

> Two seeded students share mobile `9999999999` (sibling demo).

## MySQL Configuration

In `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bli_internship
DB_USERNAME=root
DB_PASSWORD=
```

## API Base URL

```
http://localhost:8000/api/v1
```

### Standard response

```json
{
  "success": true,
  "message": "Student registered successfully",
  "data": {}
}
```

### Auth

- `POST /auth/login` — `{ "login": "REG2026001", "password": "..." }`
- `POST /auth/register` — student registration
- `POST /auth/forgot-password` — OTP to email
- `POST /auth/verify-otp` — reset password
- `POST /auth/logout` — Bearer token required

### Student (Bearer token, role: student)

- `GET /student/profile`
- `GET /student/group`
- `GET /student/whatsapp` — Flutter WhatsApp deep link payload
- `POST /student/daily-reports`
- `POST /student/assignments`
- `POST /student/attendance`

### Admin (Bearer token, staff roles)

- `GET /admin/dashboard`
- `GET /admin/students?mobile=9999999999` — filter same parent mobile
- `POST /admin/students/{id}/approve`
- `api/admin/groups` — CRUD, assign/unassign students
- `POST /admin/groups/{id}/whatsapp/send` — queue WhatsApp Cloud API invitations
- `GET /admin/whatsapp/messages` — logs, retry failed, resend

Import **`docs/postman_collection.json`** into Postman.

## Flutter Integration Notes

1. Store Sanctum token from login response.
2. Send header: `Authorization: Bearer {token}`.
3. Use `student_code` or `registration_no` for login — not mobile alone.
4. WhatsApp API returns `flutter_action: "open_whatsapp"` and `deep_link` URL.

## Business Rules (Important)

- **Mobile is NOT unique** — multiple students may share parent contact.
- **Unique:** `registration_no`, `student_code` (e.g. `BLI2026S001`).
- Profile fields (photo, ID proof, email, address) are optional at registration.

## Production

```bash
npm ci && npm run build
php artisan config:cache
php artisan route:cache
```

Configure mail (`MAIL_*`), queue (`QUEUE_CONNECTION=database` or `redis`), and CORS origins in `config/cors.php`.

## License

Proprietary — M/s Bhagya Laxmi
