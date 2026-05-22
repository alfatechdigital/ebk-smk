<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstitutionController;
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

    // Chat / Konsultasi
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{ticket}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{ticket}/message', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/{ticket}/messages', [ChatController::class, 'messages'])->name('chat.messages');

    // Tickets
    Route::resource('tickets', TicketController::class)->except(['edit']);
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assignTeacher'])->name('tickets.assign');

    // Catatan Konseling
    Route::get('/catatan', [NoteController::class, 'index'])->name('catatan.index');
    Route::post('/catatan', [NoteController::class, 'store'])->name('catatan.store');
    Route::put('/catatan/{note}', [NoteController::class, 'update'])->name('catatan.update');
    Route::get('/catatan/{note}/pdf', [NoteController::class, 'generatePdf'])->name('catatan.pdf');

    // Rekap Laporan
    Route::get('/rekap', [ReportController::class, 'index'])->name('rekap.index');

    // ── Admin & SuperAdmin only ──
    Route::middleware('role:admin,superadmin')->group(function () {
        Route::resource('users', UserController::class)->except(['show','create','edit']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::resource('kategori', ServiceController::class)->except(['show','create','edit']);
        Route::get('/kategori', [ServiceController::class, 'index'])->name('kategori.index');

        Route::get('/pengaturan', [InstitutionController::class, 'index'])->name('pengaturan.index');
        Route::post('/pengaturan', [InstitutionController::class, 'update'])->name('pengaturan.update');
    });

    // ── SuperAdmin only ──
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/hak-akses', fn() => view('hakakses.index'))->name('hakakses.index');
    });

    // Profile
    Route::get('/profil', [ProfileController::class, 'index'])->name('profil.index');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // Data Siswa (Guru)
    Route::get('/data-siswa', [App\Http\Controllers\StudentDataController::class, 'index'])->name('data-siswa.index');
});
