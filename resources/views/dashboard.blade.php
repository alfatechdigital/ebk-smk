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
            <h2>Dashboard Admin</h2>
            <p>Kelola user dan tiket konsultasi</p>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        @if(auth()->user()->isAdmin())
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3>{{ $stats['total_siswa'] }}</h3>
                    <p>Total Siswa</p>
                </div>
            </div>
            <div class="stat-card slate">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>{{ $stats['total_siswa_x'] }}</h3>
                    <p>Siswa Kelas X</p>
                </div>
            </div>
            <div class="stat-card gold">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>{{ $stats['total_siswa_xi'] }}</h3>
                    <p>Siswa Kelas XI</p>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>{{ $stats['total_siswa_xii'] }}</h3>
                    <p>Siswa Kelas XII</p>
                </div>
            </div>
        @else
            @if(auth()->user()->isSiswa())
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="stat-info">
                        <h3 style="font-size:18px">{{ $assignedGuru ?? '-' }}</h3>
                        <p>Guru BK Kelas Anda</p>
                    </div>
                </div>
            @elseif(auth()->user()->isGuru())
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>{{ $stats['total_siswa'] }}</h3>
                        <p>Total Siswa Diampu</p>
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
        @endif
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

    @if(auth()->user()->isAdmin())
        <!-- Daftar Guru BK & Kelas Diampu -->
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Daftar Guru BK & Kelas Binaannya</div>
                    <div class="card-subtitle">Daftar guru bimbingan konseling dan kelas yang diampu masing-masing</div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">No</th>
                            <th>Nama Guru BK</th>
                            <th>NIP</th>
                            <th>Kelas Diampu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $i => $teacher)
                            <tr>
                                <td style="text-align: center;">{{ $i + 1 }}</td>
                                <td><strong>{{ $teacher->user?->name ?? '-' }}</strong></td>
                                <td>{{ $teacher->nip ?? '-' }}</td>
                                <td>
                                    @forelse($teacher->classes as $class)
                                        <span class="badge badge-outline" style="border: 1px solid var(--teal); color: var(--teal); background: #f0fdfa; margin-right: 4px; display: inline-block; margin-bottom: 4px;">
                                            <i class="fas fa-chalkboard-teacher" style="margin-right: 4px;"></i>{{ $class->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted" style="font-style: italic; font-size: 0.85rem;">Belum mengampu kelas</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted" style="text-align:center">Belum ada data Guru BK</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
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
                            <th>Tanggal & Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTickets as $ticket)
                            <tr>
                                <td>{{ $ticket->code }}</td>
                                <td>
                                    @if(auth()->user()->isSiswa())
                                        {{ $ticket->anonymous ? 'Kamu (Anonim)' : $ticket->student_name ?? $ticket->student?->user?->name ?? '-' }}
                                    @else
                                        {{ $ticket->student_name ?? $ticket->student?->user?->name ?? '-' }}
                                        @if($ticket->anonymous) <span class="badge badge-warning"
                                        style="font-size:9px;padding:2px 6px;margin-left:4px">Anonim</span> @endif
                                    @endif
                                </td>
                                <td>{{ $ticket->service?->name ?? '-' }}</td>
                                <td>{{ $ticket->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                                <td><span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted" style="text-align:center">Belum ada tiket</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection