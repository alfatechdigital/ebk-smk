<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') – E-BK Sistem Bimbingan Konseling</title>
<meta name="description" content="E-BK: Platform konsultasi rahasia berbasis web untuk mendukung keterbukaan siswa dalam mengatasi masalah">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/ebk.css') }}" rel="stylesheet">
@stack('styles')
</head>
<body>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ========== SIDEBAR ========== -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">
            <div class="icon"><i class="fas fa-hands-helping"></i></div>
            <div class="text">
                <h3>E-BK</h3>
                <p>Konseling Online</p>
            </div>
        </div>
    </div>

    <div class="sidebar-nav">
        @php $role = auth()->user()->role; @endphp

        {{-- Utama --}}
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i><span>Dashboard</span>
        </a>

        @if($role === 'guru')
        <a href="{{ route('profil.index') }}" class="nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i><span>Profil Saya</span>
        </a>
        @endif

        {{-- Manajemen (admin & superadmin) --}}
        @if(in_array($role, ['admin','superadmin']))
        <div class="nav-section-label">Manajemen</div>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fas fa-users-cog"></i><span>Manajemen User</span>
        </a>
        @if($role === 'superadmin')
        <a href="{{ route('hakakses.index') }}" class="nav-item {{ request()->routeIs('hakakses.*') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i><span>Hak Akses Menu</span>
        </a>
        @endif
        <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i><span>Semua Tiket</span>
        </a>
        <a href="{{ route('kategori.index') }}" class="nav-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i><span>Kategori Layanan</span>
        </a>
        <div class="nav-section-label">Sistem</div>
        <a href="{{ route('pengaturan.index') }}" class="nav-item {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i><span>Pengaturan Lembaga</span>
        </a>
        <a href="{{ route('rekap.index') }}" class="nav-item {{ request()->routeIs('rekap.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i><span>Rekap Laporan</span>
        </a>
        @endif

        {{-- Guru BK --}}
        @if($role === 'guru')
        <div class="nav-section-label">Konseling</div>
        <a href="{{ route('data-siswa.index') }}" class="nav-item {{ request()->routeIs('data-siswa.*') ? 'active' : '' }}">
            <i class="fas fa-user-graduate"></i><span>Data Siswa</span>
        </a>
        <a href="{{ route('catatan.index') }}" class="nav-item {{ request()->routeIs('catatan.*') ? 'active' : '' }}">
            <i class="fas fa-notes-medical"></i><span>Catatan Konseling</span>
        </a>
        <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i><span>Layanan Konsultasi</span>
            @php $unread = \App\Models\Ticket::where('teacher_id', auth()->user()->teacher?->id)->where('status','menunggu')->count(); @endphp
            @if($unread > 0)<span class="badge-nav">{{ $unread }}</span>@endif
        </a>
        <div class="nav-section-label">Laporan</div>
        <a href="{{ route('rekap.index') }}" class="nav-item {{ request()->routeIs('rekap.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i><span>Rekap Laporan</span>
        </a>
        @endif

        {{-- Siswa --}}
        @if($role === 'siswa')
        <div class="nav-section-label">Konsultasi</div>
        <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
            <i class="fas fa-ticket-alt"></i><span>Tiket Saya</span>
        </a>
        <a href="{{ route('tickets.create') }}" class="nav-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
            <i class="fas fa-plus-circle"></i><span>Ajukan Konsultasi</span>
        </a>
        <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i><span>Chat Konsultasi</span>
        </a>
        @endif
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</nav>

<!-- ========== TOPBAR ========== -->
<header class="topbar">
    <button class="topbar-hamburger" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="topbar-title">
        <span>@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-user">
        <div class="topbar-avatar">{{ auth()->user()->avatar_initials }}</div>
        <div>
            <div class="topbar-username">{{ auth()->user()->name }}</div>
            <div class="topbar-role">{{ auth()->user()->role_label }}</div>
        </div>
    </div>
</header>

<!-- ========== MAIN CONTENT ========== -->
<main class="main-content">
    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>
@stack('scripts')
</body>
</html>
