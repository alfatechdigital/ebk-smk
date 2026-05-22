# EBK Web System – Implementation Plan

## Goal Description
Create a web‑based EBK (E‑Bimbingan Konseling) system using **Laravel 11**, **SQLite (WAL mode)**, **Pusher** for realtime messaging, and **Tailwind CSS** with a rounded‑font theme. The system must support multi‑role authentication, ticket workflow, realtime chat (text + media), data compression, and be optimized for shared‑hosting environments (≈400 concurrent users) with eager loading and comprehensive error handling.

## User Review Required
> [!IMPORTANT] Please confirm the following before we start coding:
> - Database schema (any additional fields?)
> - UI components needed beyond `e-bk-system.html` (mobile navigation, modals, etc.)
> - Any third‑party packages you want to include (e.g., `spatie/laravel-image-optimizer`, `barryvdh/laravel-dompdf`).

## Open Questions
- Should the "Guru BK" profile include fields: `nama`, `nip`, `kelas_diampu`, `no_whatsapp`, `foto_profil`?
- Desired max upload sizes for media (image, video, audio)?
- Do you want anonymous reporting to hide student identity completely or just hide student name?
- Preferred PDF library for journal export?

## Proposed Changes
### 1. Composer Packages
- `laravel/breeze` (auth scaffolding)
- `pusher/pusher-php-server`
- `spatie/laravel-image-optimizer`
- `barryvdh/laravel-dompdf`
- `livewire/livewire` (optional for interactive UI)

### 2. Environment Configuration
- `.env` entries for `BROADCAST_DRIVER=pusher`, `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`.
- SQLite WAL mode enabled in `AppServiceProvider` (`DB::statement('PRAGMA journal_mode=WAL;');`).

### 3. Database Migrations
| Table | Key Columns |
|-------|-------------|
| `users` | `id`, `name`, `email`, `password`, `role` (enum) |
| `institutes` | `id`, `name`, `address`, `logo_path` |
| `classes` | `id`, `name`, `institute_id`, `archived` |
| `students` | `id`, `user_id`, `class_id`, `profile` (JSON) |
| `teachers` (guru_bk) | `id`, `user_id`, `institute_id`, `profile` (JSON) |
| `services` | `id`, `name`, `description` |
| `tickets` | `id`, `student_id`, `service_id`, `teacher_id`, `status`, `type`, `priority` (low/medium/high), `anonymous` (bool), `scheduled_at`, timestamps |
| `ticket_messages` | `id`, `ticket_id`, `sender_id`, `type` (text/image/video/audio), `content` (text or file path), timestamps |
| `counseling_notes` | `id`, `ticket_id`, `teacher_id`, `note` (HTML), timestamps |
| `journals` | `id`, `ticket_id`, `pdf_path`, timestamps |

All migrations will set `PRAGMA journal_mode=WAL;` in `AppServiceProvider`.

### 4. Models & Relationships
- `User` hasOne `Student` / `Teacher` depending on role.
- `Teacher` belongsTo `Institute`.
- `Ticket` belongsTo `Student`, `Teacher` (optional), `Service`.
- `Ticket` hasMany `TicketMessage`.
- `Ticket` hasOne `CounselingNote`.
- `CounselingNote` hasOne `Journal`.

### 5. Controllers & Services
- **AuthController** – Laravel Breeze routes, role‑based redirects.
- **TicketController** – CRUD, `store` with preview step, `assignTeacher`, `updateStatus`.
- **ChatController** – `fetchMessages($ticketId)`, `sendMessage(Request $request)` (validation, compression, storage, broadcasting).
- **NoteController** – `store` after ticket completion, generate PDF via DOMPDF.
- **DashboardController** – role‑specific dashboards.
- **ReportController** – handle anonymous "Laporkan Teman" flow.

### 6. Real‑time Chat Implementation
- **Backend**: Laravel events (`MessageSent`) implements `ShouldBroadcast` on private channel `private-ticket.{ticketId}`.
- **Frontend**: Tailwind + Alpine.js (or Livewire) UI, file input with client‑side compression (canvas for images), MediaRecorder for audio, preview thumbnails.
- **Broadcasting**: Pusher credentials via `.env`, `QUEUE_CONNECTION=database` for queued broadcasts.
- **Store‑and‑Forward**: Messages persisted; when a teacher reconnects, pending messages are broadcast automatically.

### 7. Media Handling & Compression
- Use `spatie/laravel-image-optimizer` for images.
- Video/audio stored as original but size‑limited (e.g., 5 MB for video, 2 MB for audio).
- Files saved under `storage/app/public/chat/{ticketId}` and symlinked via `php artisan storage:link`.

### 8. Privacy & Authorization
- Policies (`TicketPolicy`) restrict access: students view own tickets, teachers view tickets assigned to them, admin/superadmin view all.
- Anonymous tickets: `student_id` null, `anonymous` true; UI masks student name.
- Guru BK can only view tickets where `teacher_id` matches their user ID.

### 9. PDF Export
- After a ticket is marked **completed**, `NoteController` generates a PDF using `barryvdh/laravel-dompdf` and stores path in `journals`.
- Provide download button on ticket view.

### 10. UI Integration (from `e-bk-system.html`)
- Convert static HTML to Blade templates (`layouts/app.blade.php`, `dashboard.blade.php`, `ticket/create.blade.php`, `chat.blade.php`).
- Apply Tailwind utilities, rounded‑font via Google Font "Inter" with `font-rounded` class.
- Ensure mobile‑responsive layout (flex, grid, hidden menus for small screens).
- Use Alpine.js for toggling chat panels, file previews, and real‑time updates.

### 11. Optimizations for Shared Hosting
- **Eager Loading** on all queries (`with([...])`).
- **SQLite WAL** for concurrent reads/writes.
- **Cache** static lookup tables (services, institutes) using file cache.
- **Queue** broadcast jobs (`database` driver) to avoid blocking HTTP requests.
- **Asset Minification** via Laravel Mix (Tailwind purge, CSS/JS minify).

## Verification Plan
### Automated Tests
- PHPUnit feature tests for authentication, role access, ticket lifecycle, media upload validation.
- Laravel Dusk (or Playwright) UI tests for responsive layout, chat real‑time flow, PDF download.
### Manual Checks
- Deploy on a typical shared‑hosting environment (cPanel, PHP 8.2).
- Simulate 400 concurrent users with `k6` testing SQLite WAL concurrency.
- Verify Pusher updates across multiple browsers/devices.
- Test media compression results and file size limits.
- Confirm PDF export renders correctly on different browsers.

## Next Steps
Once you confirm the schema, UI components, and any additional preferences, we will generate migration files, model stubs, controller skeletons, Blade templates, and the initial real‑time chat scaffolding.
