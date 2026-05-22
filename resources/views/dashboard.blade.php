@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <h2 id="dash-greeting">
        @php
            $greet = match(auth()->user()->role) {
                'superadmin' => 'Dashboard Super Admin',
                'admin'      => 'Dashboard Admin',
                'guru'       => 'Dashboard Guru BK',
                'siswa'      => 'Halo, ' . auth()->user()->name . '! 👋',
                default      => 'Dashboard',
            };
        @endphp
        {{ $greet }}
    </h2>
    <p>
        @php
            $sub = match(auth()->user()->role) {
                'superadmin' => 'Pantau seluruh aktivitas sistem E-BK',
                'admin'      => 'Kelola user dan tiket konsultasi',
                'guru'       => 'Ringkasan aktivitas konseling kamu',
                'siswa'      => 'Selamat datang di layanan konseling online',
                default      => 'Ringkasan aktivitas sistem E-BK',
            };
        @endphp
        {{ $sub }}
    </p>
</div>

{{-- Stats Grid --}}
<div class="stats-grid">
    @foreach($stats as $s)
    <div class="stat-card {{ $s['cls'] }}">
        <div class="stat-icon"><i class="{{ $s['icon'] }}"></i></div>
        <div class="stat-info">
            <h3>{{ $s['val'] }}</h3>
            <p>{{ $s['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="grid-2">
    {{-- Recent Tickets --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Tiket Terbaru</div>
                <div class="card-subtitle">5 tiket terakhir masuk</div>
            </div>
            <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>ID</th><th>Siswa</th><th>Layanan</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                    <tr>
                        <td>{{ $ticket->code }}</td>
                        <td>
                            @if($ticket->anonymous)
                                <span class="text-muted">Anonim</span>
                            @else
                                {{ $ticket->student?->user?->name ?? '—' }}
                            @endif
                        </td>
                        <td>{{ $ticket->service?->name ?? '—' }}</td>
                        <td><span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-state" style="text-align:center;padding:24px;color:var(--muted)">Belum ada tiket</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Aktivitas Terkini</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px">
            @forelse($activities as $act)
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div style="width:32px;height:32px;background:rgba(13,124,102,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:13px;flex-shrink:0">
                    <i class="{{ $act['icon'] }}"></i>
                </div>
                <div>
                    <p style="font-size:13px;font-weight:600;color:var(--charcoal)">{{ $act['text'] }}</p>
                    <p class="text-muted">{{ $act['time'] }}</p>
                </div>
            </div>
            @empty
            <p class="text-muted">Belum ada aktivitas</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
