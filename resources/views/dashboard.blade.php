@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-header">
        @if(auth()->user()->isSiswa())
            <h2>Halo, {{ auth()->user()->name }}! 👋</h2>
            <p>Selamat datang di layanan konseling online</p>
        @elseif(auth()->user()->isGuru())
            <h2>Dashboard Guru BK</h2>
            <p>Ringkasan aktivitas konseling kamu</p>
        @elseif(auth()->user()->isAdmin())
            <h2>Dashboard {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</h2>
            <p>{{ auth()->user()->isSuperAdmin() ? 'Pantau seluruh aktivitas sistem E-BK' : 'Kelola user dan tiket konsultasi' }}
            </p>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        @if(auth()->user()->isSiswa())
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-info">
                    <h3 style="font-size:18px">{{ $assignedGuru ?? '-' }}</h3>
                    <p>Guru BK Kelas Anda</p>
                </div>
            </div>
        @elseif(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isGuru())
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3>{{ $stats['total_siswa'] }}</h3>
                    <p>Total Siswa {{ auth()->user()->isGuru() ? 'Diampu' : '' }}</p>
                </div>
            </div>
        @endif
        <a href="{{ route('tickets.index') }}" class="stat-card slate" style="text-decoration: none; color: inherit;">
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['total_konsultasi'] }}</h3>
                <p>Total Konsultasi Aktif</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'menunggu']) }}" class="stat-card gold" style="text-decoration: none; color: inherit;">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['menunggu'] }}</h3>
                <p>Menunggu Respon</p>
            </div>
        </a>
        <a href="{{ route('tickets.index', ['status' => 'diproses']) }}" class="stat-card danger" style="text-decoration: none; color: inherit;">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['diproses'] }}</h3>
                <p>Sedang Diproses</p>
            </div>
        </a>
    </div>

    @if(auth()->user()->isGuru() && isset($classActivities) && $classActivities->count() > 0)
        <div class="card mb-20">
            <div class="card-header">
                <div class="card-title">Aktivitas Tiket Per Kelas (Diampu)</div>
            </div>
            <div class="class-activity-grid">
                @foreach($classActivities as $ca)
                    <div class="class-activity-card">
                        <div class="class-activity-count">{{ $ca['count'] }}</div>
                        <div class="class-activity-name">{{ $ca['class_name'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid-2">
        <!-- Recent Tickets -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Tiket Terbaru</div>
                    <div class="card-subtitle">5 tiket terakhir masuk</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Siswa</th>
                            <th>Layanan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTickets as $ticket)
                            <tr>
                                <td>{{ $ticket->code }}</td>
                                <td>
                                    @if(auth()->user()->isSiswa())
                                        {{ $ticket->anonymous ? 'Kamu (Anonim)' : $ticket->student?->user?->name ?? '-' }}
                                    @else
                                        {{ $ticket->student?->user?->name ?? '-' }}
                                        @if($ticket->anonymous) <span class="badge badge-warning"
                                        style="font-size:9px;padding:2px 6px;margin-left:4px">Anonim</span> @endif
                                    @endif
                                </td>
                                <td>{{ $ticket->service?->name ?? '-' }}</td>
                                <td><span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted" style="text-align:center">Belum ada tiket</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Aktivitas Terkini</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:14px">
                @forelse ($activities as $act)
                    <div style="display:flex;gap:12px;align-items:flex-start">
                        <div
                            style="width:32px;height:32px;background:rgba(13,124,102,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:13px;flex-shrink:0">
                            <i class="{{ $act['icon'] }}"></i>
                        </div>
                        <div>
                            <p style="font-size:13px;font-weight:600;color:var(--charcoal)">{{ $act['text'] }}</p>
                            <p class="text-muted">{{ $act['time'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>Belum ada aktivitas</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection