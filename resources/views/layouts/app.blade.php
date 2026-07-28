<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-BK — @yield('title', 'Sistem Bimbingan Konseling Online')</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/ebk.css') }}" rel="stylesheet">
    <style>
        /* CSS Tambahan untuk Dropdown Sidebar */
        .nav-dropdown {
            display: none;
            background: rgba(0, 0, 0, 0.03);
            padding-left: 15px;
        }

        .nav-item.has-dropdown {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-item.has-dropdown .chevron {
            transition: transform 0.3s;
            font-size: 0.8rem;
        }

        .nav-item.has-dropdown.open .chevron {
            transform: rotate(180deg);
        }

        .nav-item.has-dropdown.open+.nav-dropdown {
            display: block;
        }

        .nav-dropdown .nav-item {
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 0;
            border-left: 2px solid transparent;
        }

        .nav-dropdown .nav-item.active {
            background: transparent;
            color: var(--teal);
            border-left: 2px solid var(--teal);
        }
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
                    ['route' => 'users.index', 'params' => ['role' => 'admin'], 'label' => 'Admin'],
                    ['route' => 'users.index', 'params' => ['role' => 'guru'], 'label' => 'Guru BK'],
                    ['route' => 'users.index', 'params' => ['role' => 'siswa'], 'label' => 'Siswa'],
                ];

                $menus = [
                    'admin' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'profil.index', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Saya'],
                        ['section' => 'Manajemen'],
                        ['route' => 'kelas.index', 'icon' => 'fas fa-school', 'label' => 'Data Kelas'],
                        ['label' => 'Manajemen User', 'icon' => 'fas fa-users-cog', 'submenu' => $userSubmenu],
                        ['route' => 'kategori.index', 'icon' => 'fas fa-tags', 'label' => 'Kategori Layanan'],
                        ['section' => 'Sistem'],
                        ['route' => 'pengaturan.index', 'icon' => 'fas fa-cog', 'label' => 'Pengaturan Sistem'],
                    ],
                    'guru' => [
                        ['section' => 'Utama'],
                        ['route' => 'dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
                        ['route' => 'profil.index', 'icon' => 'fas fa-user-circle', 'label' => 'Profil Saya'],
                        ['section' => 'Konseling'],
                        ['route' => 'data-siswa.index', 'icon' => 'fas fa-user-graduate', 'label' => 'Data Siswa'],
                        ['route' => 'tickets.index', 'icon' => 'fas fa-comments', 'label' => 'Layanan Konsultasi'],
                        ['section' => 'Laporan'],
                        ['route' => 'catatan.index', 'icon' => 'fas fa-clipboard-list', 'label' => 'Catatan Konseling'],
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
                        foreach ($item['submenu'] as $sub) {
                            if (request()->fullUrlIs(route($sub['route'], $sub['params'] ?? []))) {
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
                    <a href="{{ route($item['route']) }}"
                        class="nav-item {{ request()->routeIs($item['route'] . '*') ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span
                            style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 8px; min-width: 0;">
                            <span
                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item['label'] }}</span>
                            @if($item['route'] === 'tickets.index')
                                <div class="sidebar-badges-container" style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                                    @if(auth()->user()->isGuru())
                                        <span class="sidebar-waiting-badge"
                                             style="display: none; position: relative; width: 20px; height: 20px; align-items: center; justify-content: center; flex-shrink: 0;" title="Tiket Menunggu">
                                             <i class="fas fa-clock" style="font-size: 15px; color: currentColor; opacity: 0.7;"></i>
                                             <span class="count"
                                                 style="position: absolute; top: -4px; right: -6px; background: #f59e0b; color: white; border-radius: 50%; width: 14px; height: 14px; font-size: 8px; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1; border: 1px solid white;">0</span>
                                         </span>
                                    @endif
                                    <span class="sidebar-unread-badge"
                                         style="display: none; position: relative; width: 20px; height: 20px; align-items: center; justify-content: center; flex-shrink: 0;">
                                         <i class="fas fa-comment" style="font-size: 15px; color: currentColor; opacity: 0.7;"></i>
                                         <span class="count"
                                             style="position: absolute; top: -4px; right: -6px; background: #ef4444; color: white; border-radius: 50%; width: 14px; height: 14px; font-size: 8px; font-weight: 700; display: flex; align-items: center; justify-content: center; line-height: 1; border: 1px solid white;">0</span>
                                     </span>
                                </div>
                            @endif
                        </span>
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

    <div class="sidebar-overlay" id="sidebar-overlay"
        onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('open')"></div>

    <header class="topbar">
        <button class="topbar-hamburger"
            onclick="document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebar-overlay').classList.toggle('open')">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title"><span>@yield('page-title', 'Dashboard')</span></div>
        <div class="topbar-user-container" style="position: relative;">
            <div class="topbar-user" onclick="toggleUserDropdown(event)">
                <div class="topbar-avatar">{{ $user->avatar_initials }}</div>
                <div style="text-align: left;" class="topbar-user-info">
                    <div class="topbar-username" style="display: flex; align-items: center; gap: 6px;">
                        <span>{{ $user->name }}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--muted);"></i>
                    </div>
                    <div class="topbar-role">{{ $user->role_label }}</div>
                </div>
            </div>

            <div id="user-dropdown"
                style="display: none; position: absolute; top: calc(100% + 10px); right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); width: 180px; z-index: 1000; overflow: hidden; animation: fadeIn 0.2s ease; text-align: left;">
                <a href="{{ route('profil.index') }}"
                    style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-size: 13px; color: var(--slate); text-decoration: none; transition: background 0.2s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <i class="fas fa-user-circle" style="color: #64748b; font-size: 15px;"></i> Profil Saya
                </a>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                    @csrf
                    <button type="submit"
                        style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-size: 13px; color: #ef4444; width: 100%; border: none; background: transparent; text-align: left; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-sign-out-alt" style="font-size: 14px;"></i> Keluar / Logout
                    </button>
                </form>
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

    @if(env('PUSHER_APP_KEY'))
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    @endif

    <script>
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) {
                if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }
        }

        window.addEventListener('click', function (event) {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown && dropdown.style.display === 'block') {
                const container = event.target.closest('.topbar-user-container');
                if (!container) {
                    dropdown.style.display = 'none';
                }
            }
            if (!event.target.closest('.ticket-actions-dropdown')) {
                window.closeAllDropdowns();
            }
        });

        window.toggleActionsDropdown = function (event, id) {
            event.stopPropagation();
            const targetDropdown = document.getElementById('actions-dropdown-' + id);
            const allDropdowns = document.querySelectorAll('.dropdown-menu-content');

            // Reset z-index on all cards first
            document.querySelectorAll('.ticket-card').forEach(card => {
                card.style.zIndex = '';
            });

            allDropdowns.forEach(dd => {
                if (dd !== targetDropdown) {
                    dd.style.display = 'none';
                }
            });
            if (targetDropdown) {
                const card = targetDropdown.closest('.ticket-card');
                if (targetDropdown.style.display === 'none' || targetDropdown.style.display === '') {
                    targetDropdown.style.display = 'block';
                    if (card) {
                        card.style.zIndex = '15'; // Lift active card above the rest
                    }
                } else {
                    targetDropdown.style.display = 'none';
                }
            }
        };

        window.closeAllDropdowns = function () {
            const allDropdowns = document.querySelectorAll('.dropdown-menu-content');
            allDropdowns.forEach(dd => {
                dd.style.display = 'none';
            });
            // Reset z-index on all cards
            document.querySelectorAll('.ticket-card').forEach(card => {
                card.style.zIndex = '';
            });
        };

        window.updateSidebarUnreadBadge = function (count, waitingCount) {
            const badge = document.querySelector('.sidebar-unread-badge');
            if (badge) {
                const c = count || 0;
                if (c > 0) {
                    const countSpan = badge.querySelector('.count');
                    if (countSpan) {
                        countSpan.textContent = c > 99 ? '99+' : c;
                    }
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            const waitingBadge = document.querySelector('.sidebar-waiting-badge');
            if (waitingBadge) {
                const w = waitingCount || 0;
                if (w > 0) {
                    const countSpan = waitingBadge.querySelector('.count');
                    if (countSpan) {
                        countSpan.textContent = w > 99 ? '99+' : w;
                    }
                    waitingBadge.style.display = 'inline-flex';
                } else {
                    waitingBadge.style.display = 'none';
                }
            }
        };

        @auth
            window.pollSidebarUnread = function() {
                // Skip layout polling if index page has a active local poll to avoid double network requests
                if (window._isLocalTicketsPollingActive) {
                    return;
                }
                fetch('{{ route("tickets.unread_counts") }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data) {
                            window.updateSidebarUnreadBadge(data.total_unread, data.total_waiting);
                        }
                    })
                    .catch(err => console.error('Error fetching unread count:', err));
            };

            // Run initial load and set 3-second interval
            window.pollSidebarUnread();
            setInterval(window.pollSidebarUnread, 3000);
        @endauth
    </script>
    @stack('modals')
    @stack('scripts')
</body>

</html>