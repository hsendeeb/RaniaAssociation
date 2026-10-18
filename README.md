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

## Design system — "أكاديمي رصين" (Emerald & Sand)

The public site's personality is **professional, trustworthy, institutional**. It
shares the emerald family with the Filament admin so the whole product feels like
one brand.

| Token            | Value                                   | Usage                          |
| ---------------- | --------------------------------------- | ------------------------------ |
| Primary          | emerald `#047857` (`--primary`)         | buttons, links, accents        |
| Accent           | sand `--accent` / `bg-sand-50`          | soft section backgrounds       |
| Highlight        | gold `--gold-400` (`#fbbf24`)           | "مميّزة" badges, minor accents |
| Font             | **Tajawal** (400/500/700/800)           | all UI text                    |
| Radius           | `--radius: 0.75rem`                     | cards, inputs, buttons         |
| Brand scale      | `brand.50–950` in `tailwind.config.js`  | gradients, tints               |

Semantic tokens live in `resources/css/app.css` (light + dark). Extra utility
scales (`brand`, `sand`, `gold`), a centered `container`, and a `fade-up`
animation are defined in `tailwind.config.js`. Layouts are mobile-first and
responsive at the `sm` / `md` / `lg` breakpoints; RTL is driven by the locale.

### Public routes

| Route                          | Name                | Description                    |
| ------------------------------ | ------------------- | ------------------------------ |
| `/`                            | `home`              | Landing page                   |
| `/courses`                     | `courses.index`     | Catalog with search + filters  |
| `/courses/{course:slug}`       | `courses.show`      | Course detail + lessons        |
| `/instructors`                 | `instructors.index` | Instructor directory           |
| `/instructors/{instructor}`    | `instructors.show`  | Instructor profile + courses   |
| `/about`                       | `about`             | About the association          |
| `/contact`                     | `contact`           | Contact form (POST `contact.send`) |

Only `published` courses and `active` instructors/categories are exposed publicly;
unpublished records return a 404.

### Course content locking

The course page shows info (title, summary, description, price, instructor, related
courses) to everyone, but the **lessons and videos are locked**:

- **Guests** see a locked card with log in / register prompts.
- **Students** can view lesson content only after the admin approves their enrolment
  (status `active`). Pending and cancelled requests stay locked, with a status notice.
- **Free courses follow the same approval flow** — there is no auto-unlock for them.
- Admins (`is_admin`) can preview any course's content for review purposes.
- Lessons are never sent to the browser for unauthorised visitors — the controller
  only loads the `lessons` relation when access is granted (`canViewContent`).

A lesson's body can be a **video**, **rich-text content**, an **attachment**, or any
combination. A lesson only shows as locked (🔒) when it has none of the three — the UI
treats text and attachments as openable, so a text/handout-only lesson is never stuck.

### Student area (requires an account)

| Route                                | Name                  | Description                     |
| ------------------------------------ | --------------------- | ------------------------------- |
| `/dashboard`                         | `dashboard`           | "لوحتي" — the student's courses |
| `/courses/{course:slug}/enroll`      | `enrollments.create`  | Enrolment form                  |
| `POST /courses/{course:slug}/enroll` | `enrollments.store`   | Submit a request (pending)      |
| `DELETE /enrollments/{enrollment}`   | `enrollments.destroy` | Cancel a pending/active request |

## Enrollment flow

Account-based, with admin approval:

1. On a course page, "سجّل في الدورة" links to the enrolment form. Guests are sent
   to login and returned to the form afterwards (Laravel's `intended` URL).
2. The form collects **name, email, phone** (prefilled from the account). Submitting
   creates/updates the student's `Student` record and an `Enrollment` with status
   **`قيد الانتظار` (pending)**.
3. Admins review requests at **/admin/enrollments** — row and bulk actions **موافقة**
   (→ `active`, sets `enrolled_at`) and **إلغاء** (→ `cancelled`).
4. Students track everything on **لوحتي** (`/dashboard`): status badges, progress,
   plus cancel and re-enrol actions.

Rules enforced in `EnrollmentController` and `Course`:

- One enrolment per student + course (duplicates are rejected with a flash message).
- Capacity: `Course::hasAvailableSeats()` counts pending + active enrolments, so a
  full course cannot be requested.
- Cancelling keeps the row (status `cancelled`) and re-requesting revives it via
  `updateOrCreate`, so the `(student_id, course_id)` unique index is never violated.
- Students can only cancel their own enrolments (otherwise `403`).
- Only students with an `active` enrolment can view a course's lessons — see
  *Course content locking* above.

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
├── Http/
│   ├── Controllers/Site/         # Home, Course, Instructor, Page, Enrollment, Dashboard
│   └── Resources/                # Course/Instructor/Category/Lesson/Enrollment resources
├── Models/                       # Domain models
└── Providers/Filament/
    └── AdminPanelProvider.php    # Panel config: brand, RTL font, colors, nav groups

lang/ar/                          # Arabic translations (validation, auth, ...)
resources/
├── css/app.css                   # Design tokens (light + dark)
├── js/
│   ├── components/site/          # SiteHeader/Footer, CourseCard, StatusBadge, ...
│   ├── layouts/site/SiteLayout.vue
│   ├── pages/Dashboard.vue       # "لوحتي" — the student area
│   └── pages/site/               # Home, Courses/{Index,Show,Enroll}, Instructors/*, ...
└── views/app.blade.php           # Root view (dir="rtl")
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

1. ✅ **Public site (Inertia/Vue)** — done. Home, courses index/show, instructors,
   about, and contact pages — Arabic/RTL, responsive, built on the design tokens below.
2. ✅ **Enrollment flow** — done. Account-based enrolment: students register/log in,
   request a course (status `pending`), the admin approves it in Filament, and the
   student tracks it on **لوحتي**. See *Enrollment flow* below.
3. **Roles & permissions** — `spatie/laravel-permission` (or a `role` column) to add
   editor/support roles alongside `is_admin`.
4. **Media** — course covers and avatars use the `public` disk; consider a CDN/S3 driver
   for production.
5. **Payments** — integrate a payment gateway that supports Lebanon (e.g. PayTabs,
   Checkout.com) for paid courses.
6. **Notifications** — email/SMS on enrollment and course updates.
7. **Reports** — Filament widgets for enrollments, revenue, and popular courses.
8. **Performance & prod** — `php artisan filament:cache-components`, `php artisan optimize`,
   queue worker, and MySQL.

## Notes

- **Course slugs** are generated automatically: the `title` field is `live(onBlur: true)`
  and fills `slug` with Laravel's `Str::slug()` (Arabic is transliterated, e.g.
  `دورة أساسيات الويب` → `dor-asasyat-aloyb`). The slug stays editable; on **edit** it is
  only regenerated if it is empty, so live course URLs don't change silently.
- **Uploads must use the `public` disk**: `FILESYSTEM_DISK=public` in `.env` (the default
  in `.env.example`). Uploads made while it is set to `local` land in
  `storage/app/private` and are **not** reachable from the site (`/storage/...` points at
  `storage/app/public`), which shows up as broken images. Filament upload fields and image
  columns are pinned to `->disk('public')` so they keep working regardless of the default.
  Don't forget `php artisan storage:link` and `php artisan optimize:clear` after changing it.

- **MySQL later**: change `DB_CONNECTION=mysql` and the `DB_*` values in `.env`.
- **Enrollment uniqueness**: the public flow guards duplicates and revives cancelled
  rows via `updateOrCreate`. The **admin** form still surfaces the
  `(student_id, course_id)` unique violation as a database error — consider adding a
  form-level unique rule there.
- **API resource wrapping**: `JsonResource::withoutWrapping()` is enabled in
  `AppServiceProvider`. Inertia serialises resources through their `Responsable`
  contract, which would otherwise nest props under `data`. Single resources and
  plain collections are now flat; paginated collections still expose `data`,
  `links`, and `meta`.
- **RTL**: the direction is driven by the locale. Filament renders RTL automatically
  because `ar` is the app locale.
- **PHP 8.5**: `config/database.php` selects the correct `PDO`/`Pdo\Mysql` SSL constant
  per PHP version to avoid the 8.5 deprecation notice.
