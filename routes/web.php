<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ServiceController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ClassController;
use Illuminate\Support\Facades\Route;

// ─── Auth ───────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', fn() => redirect()->route('login'));

// ─── Authenticated routes ────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chat & Tickets: only GURU and SISWA can access
    Route::middleware('role:guru,siswa')->group(function () {
        // Chat / Konsultasi
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/{ticket}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/chat/{ticket}/message', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::get('/chat/{ticket}/messages', [ChatController::class, 'messages'])->name('chat.messages');

        // Tickets
        Route::get('/tickets/unread-counts', [TicketController::class, 'unreadCounts'])->name('tickets.unread_counts');
        Route::resource('tickets', TicketController::class)->except(['edit']);
        Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
        Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assignTeacher'])->name('tickets.assign');
        Route::post('/tickets/{ticket}/toggle-favorite', [TicketController::class, 'toggleFavorite'])->name('tickets.favorite');
        Route::post('/tickets/{ticket}/toggle-pinned', [TicketController::class, 'togglePinned'])->name('tickets.pinned');
    });

    // Catatan Konseling: only GURU BK can access
    Route::middleware('role:guru')->group(function () {
        Route::get('/catatan', [NoteController::class, 'index'])->name('catatan.index');
        Route::get('/catatan/rekap', [NoteController::class, 'exportRekapPdf'])->name('catatan.rekap');
        Route::post('/catatan', [NoteController::class, 'store'])->name('catatan.store');
        Route::put('/catatan/{note}', [NoteController::class, 'update'])->name('catatan.update');
        Route::delete('/catatan/{note}', [NoteController::class, 'destroy'])->name('catatan.destroy');
        Route::get('/catatan/{note}/pdf', [NoteController::class, 'generatePdf'])->name('catatan.pdf');
    });



    // ── Admin only ──
    Route::middleware('role:admin')->group(function () {
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::resource('users', UserController::class)->except(['show','create','edit']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::resource('kelas', ClassController::class)->parameters(['kelas' => 'kelas'])->except(['show','create','edit']);



        Route::get('/pengaturan', [InstitutionController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [InstitutionController::class, 'update'])->name('pengaturan.update');

        // Jurnal Kegiatan BK
        Route::get('/jurnal', [JournalController::class, 'index'])->name('jurnal.index');
        Route::get('/jurnal/rekap', [JournalController::class, 'exportPdf'])->name('jurnal.rekap');
    });

    // Kategori Layanan (Admin & Guru BK)
    Route::middleware('role:admin,guru')->group(function () {
        Route::resource('kategori', ServiceController::class)->except(['show','create','edit']);
        Route::get('/kategori', [ServiceController::class, 'index'])->name('kategori.index');
    });

    // Profile
    Route::get('/profil', [ProfileController::class, 'index'])->name('profil.index');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Data Siswa (Guru)
    Route::get('/data-siswa', [App\Http\Controllers\StudentDataController::class, 'index'])->name('data-siswa.index');
    Route::post('/data-siswa', [App\Http\Controllers\StudentDataController::class, 'store'])->name('data-siswa.store');
    Route::put('/data-siswa/{student}', [App\Http\Controllers\StudentDataController::class, 'update'])->name('data-siswa.update');
    Route::delete('/data-siswa/{student}', [App\Http\Controllers\StudentDataController::class, 'destroy'])->name('data-siswa.destroy');
});
