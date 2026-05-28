<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-BK — @yield('title', 'Sistem Bimbingan Konseling Online')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/ebk.css') }}" rel="stylesheet">
    <style>
        /* CSS Tambahan untuk Dropdown Sidebar */
        .nav-dropdown { display: none; background: rgba(0,0,0,0.03); padding-left: 15px; }
        .nav-item.has-dropdown { cursor: pointer; display: flex; align-items: center; justify-content: space-between; }
        .nav-item.has-dropdown .chevron { transition: transform 0.3s; font-size: 0.8rem; }
        .nav-item.has-dropdown.open .chevron { transform: rotate(180deg); }
        .nav-item.has-dropdown.open + .nav-dropdown { display: block; }
        .nav-dropdown .nav-item { font-size: 0.9rem; padding: 10px 20px; border-radius: 0; border-left: 2px solid transparent; }
        .nav-dropdown .nav-item.active { background: transparent; color: var(--teal); border-left: 2px solid var(--teal); }
    </style>
    @stack('styles')
</head>
<body>
    @php $user = auth()->user(); @endphp

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
            @php
                // Logic Sub-menu Manajemen User
                $userSubmenu = [
                    ['route' => 'users.index', 'params' => ['role' => 'admin'], 'label' => 'Data Admin'],
                    ['route' => 'users.index', 'params' => ['role' => 'guru'], 'label' => 'Data Guru BK'],
                    ['route' => 'users.index', 'params' => ['role' => 'siswa'], 'label' => 'Data Siswa'],
                ];

                $menus = [
                    'superadmin' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['section' => 'Manajemen'],
                        ['label' => 'Manajemen User', 'icon' => 'fas fa-users-cog', 'submenu' => $userSubmenu],
                        ['route' => 'hakakses.index', 'icon' => 'fas fa-shield-alt', 'label' => 'Hak Akses Menu'],
                        ['route' => 'tickets.index', 'icon' => 'fas fa-ticket-alt', 'label' => 'Semua Tiket'],
                        ['route' => 'kategori.index', 'icon' => 'fas fa-tags', 'label' => 'Kategori Layanan'],
                        ['section' => 'Sistem'],
                        ['route' => 'pengaturan.index', 'icon' => 'fas fa-cog', 'label' => 'Pengaturan Lembaga'],
                        ['route' => 'rekap.index', 'icon' => 'fas fa-chart-bar', 'label' => 'Rekap Laporan'],
                    ],
                    'admin' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'profil.index', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Saya'],
                        ['section' => 'Manajemen'],
                        ['label' => 'Manajemen User', 'icon' => 'fas fa-users-cog', 'submenu' => $userSubmenu],
                        ['route' => 'kategori.index', 'icon' => 'fas fa-tags', 'label' => 'Kategori Layanan'],
                        ['section' => 'Sistem'],
                        ['route' => 'pengaturan.index', 'icon' => 'fas fa-cog', 'label' => 'Pengaturan Lembaga'],
                        ['route' => 'rekap.index', 'icon' => 'fas fa-chart-bar', 'label' => 'Rekap Laporan'],
                    ],
                    'guru' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'profil.index', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Saya'],
                        ['section' => 'Konseling'],
                        ['route' => 'data-siswa.index', 'icon' => 'fas fa-user-graduate', 'label' => 'Data Siswa'],
                        ['route' => 'catatan.index', 'icon' => 'fas fa-notes-medical', 'label' => 'Catatan Konseling'],
                        ['route' => 'tickets.index', 'icon' => 'fas fa-comments', 'label' => 'Layanan Konsultasi'],
                        ['section' => 'Laporan'],
                        ['route' => 'rekap.index', 'icon' => 'fas fa-chart-bar', 'label' => 'Rekap Laporan'],
                    ],
                    'siswa' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'profil.index', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Saya'],
                        ['section' => 'Konsultasi'],
                        ['route' => 'tickets.index', 'icon' => 'fas fa-ticket-alt', 'label' => 'Tiket Saya'],
                    ],
                ];
                $currentMenus = $menus[$user->role] ?? [];
            @endphp

            @foreach ($currentMenus as $item)
                @if (isset($item['section']))
                    <div class="nav-section-label">{{ $item['section'] }}</div>
                @elseif (isset($item['submenu']))
                    {{-- Dropdown Logic --}}
                    @php
                        $is_open = false;
                        foreach($item['submenu'] as $sub) {
                            if(request()->fullUrlIs(route($sub['route'], $sub['params'] ?? []))) {
                                $is_open = true;
                                break;
                            }
                        }
                    @endphp
                    <div class="nav-item has-dropdown {{ $is_open ? 'open' : '' }}" onclick="this.classList.toggle('open')">
                        <div>
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </div>
                        <i class="fas fa-chevron-down chevron"></i>
                    </div>
                    <div class="nav-dropdown">
                        @foreach ($item['submenu'] as $sub)
                            <a href="{{ route($sub['route'], $sub['params'] ?? []) }}" 
                               class="nav-item {{ request()->fullUrlIs(route($sub['route'], $sub['params'] ?? [])) ? 'active' : '' }}">
                                <span>{{ $sub['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ route($item['route']) }}" class="nav-item {{ request()->routeIs($item['route'].'*') ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
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

    <div class="sidebar-overlay" id="sidebar-overlay" onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('open')"></div>

    <header class="topbar">
        <button class="topbar-hamburger" onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebar-overlay').classList.toggle('open')">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title"><span>@yield('page-title', 'Dashboard')</span></div>
        <div class="topbar-user">
            <div class="topbar-avatar">{{ $user->avatar_initials }}</div>
            <div>
                <div class="topbar-username">{{ $user->name }}</div>
                <div class="topbar-role">{{ $user->role_label }}</div>
            </div>
        </div>
    </header>

    <main class="main-content">
        @if (session('success'))
            <div class="flash-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="flash-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    @stack('modals')
    @stack('scripts')
</body>
</html>