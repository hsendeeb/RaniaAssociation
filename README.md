# جمعية رانيا — منصة تعليمية

An education platform for **جمعية رانيا**: a public site built with Laravel + Vue (Inertia),
plus an admin panel powered by Filament.

## Stack

| Layer     | Technology                                                |
| --------- | --------------------------------------------------------- |
| Backend   | Laravel 12, PHP 8.2+                                       |
| Frontend  | Vue 3 + Inertia 2 + TypeScript, Tailwind CSS 3, shadcn-vue |
| Admin     | Filament 5 (Livewire) at `/admin`                          |
| Database  | SQLite (local) — swap to MySQL for production              |
| i18n      | Arabic (`ar`) first, RTL, English fallback                 |

## Requirements

- PHP 8.2+ (works on Herd's PHP 8.5)
- Composer 2
- Node.js 20+ and npm

## Quick start

```bash
composer install
npm install
cp .env.example .env        # if .env is missing
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build               # or: npm run dev
```

Then visit:

- Public site — http://raniaassociation.test
- Admin panel — http://raniaassociation.test/admin

### Admin login

Created by `AdminUserSeeder` from these `.env` values:

```
ADMIN_EMAIL=admin@rania-association.test
ADMIN_PASSWORD=password
```

> **Change `ADMIN_PASSWORD` (and the seeded admin's password) before going live.**

Only users with `is_admin = true` can open `/admin`; everyone else gets a 403.

### Optional demo content

```bash
php artisan db:seed --class=DemoContentSeeder
```

## Domain model

| Model         | Table         | Notes                                                   |
| ------------- | ------------- | ------------------------------------------------------- |
| `Category`    | categories    | Course categories (slug, active flag, sort order)        |
| `Instructor`  | instructors   | Teachers/professors, optional avatar                     |
| `Course`      | courses       | Belongs to category + instructor, level/status/price     |
| `Lesson`      | lessons       | Ordered lessons inside a course                          |
| `Student`     | students      | May link to a `User` account, guardian info              |
| `Enrollment`  | enrollments   | Pivot: student ↔ course, with status + progress          |

Relationships: `Course` has many `Lesson` and `Enrollment`; `Student` and `Course` are
joined by `Enrollment` (unique per student + course). Statuses and levels are PHP enums
in `app/Enums` (Arabic labels, Filament colors).

## Project structure

```
app/
├── Enums/                        # CourseStatus, CourseLevel, EnrollmentStatus, ...
├── Filament/Resources/           # Admin CRUD (Filament 5)
│   ├── Categories/               #   Resource + Schemas/Form + Tables/Table + Pages
│   ├── Courses/
│   ├── Lessons/
│   ├── Instructors/
│   ├── Students/
│   └── Enrollments/
├── Models/                       # Domain models
└── Providers/Filament/
    └── AdminPanelProvider.php    # Panel config: brand, RTL font, colors, nav groups

lang/ar/                          # Arabic translations (validation, auth, ...)
resources/js/                     # Inertia + Vue public app
resources/views/app.blade.php     # Root view (dir="rtl")
```

Admin resources are grouped in the sidebar as **إدارة المحتوى** (content) and
**إدارة الأفراد** (people).

## Common commands

```bash
php artisan test                          # run the test suite
php artisan migrate:fresh --seed          # rebuild the database
php artisan make:filament-resource X      # scaffold a new admin resource
php artisan lang:update                   # refresh Arabic translation files
npm run dev                               # Vite dev server
php artisan serve                         # only if not using Herd
```

## Continuing the build (roadmap)

1. **Public site (Inertia/Vue)** — replace the placeholder `Welcome` page with real
   pages: home, courses index/show, instructors, about, contact.
2. **Enrollment flow** — public registration → enrollment request → admin approval.
3. **Roles & permissions** — `spatie/laravel-permission` (or a `role` column) to add
   editor/support roles alongside `is_admin`.
4. **Media** — course covers and avatars use the `public` disk; consider a CDN/S3 driver
   for production.
5. **Payments** — integrate a Saudi gateway (Moyasar/Tap/PayTabs) for paid courses.
6. **Notifications** — email/SMS on enrollment and course updates.
7. **Reports** — Filament widgets for enrollments, revenue, and popular courses.
8. **Performance & prod** — `php artisan filament:cache-components`, `php artisan optimize`,
   queue worker, and MySQL.

## Notes

- **MySQL later**: change `DB_CONNECTION=mysql` and the `DB_*` values in `.env`.
- **Enrollment uniqueness**: `enrollments` has a unique constraint on
  `(student_id, course_id)`. A duplicate from the admin form surfaces as a database
  error — consider adding a form-level unique rule.
- **RTL**: the direction is driven by the locale. Filament renders RTL automatically
  because `ar` is the app locale.
- **PHP 8.5**: `config/database.php` selects the correct `PDO`/`Pdo\Mysql` SSL constant
  per PHP version to avoid the 8.5 deprecation notice.
