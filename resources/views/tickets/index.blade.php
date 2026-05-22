@extends('layouts.app')
@section('title', 'Tiket Konsultasi')
@section('page-title', auth()->user()->isSiswa() ? 'Konsultasi Saya' : 'Manajemen Tiket')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>{{ auth()->user()->isSiswa() ? 'Konsultasi Saya' : 'Manajemen Tiket' }}</h2>
        <p>{{ auth()->user()->isSiswa() ? 'Kelola tiket konsultasi kamu' : 'Daftar semua tiket konsultasi siswa' }}</p>
    </div>
    @if(auth()->user()->isSiswa())
    <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Konsultasi</a>
    @elseif(auth()->user()->isAdmin())
    <button class="btn btn-primary" onclick="openModal('modal-ticket')"><i class="fas fa-plus"></i> Tambah Tiket</button>
    @endif
</div>

@if(auth()->user()->isSiswa())
<div class="warning-box mb-20"><i class="fas fa-lock"></i><span>Semua konsultasi bersifat <b>rahasia</b>. Hanya kamu dan Guru BK yang dapat melihat isi percakapan.</span></div>
@endif

{{-- Filter Bar --}}
<form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Cari tiket, nama siswa..." value="{{ request('search') }}">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="menunggu" {{ request('status')=='menunggu' ? 'selected' : '' }}>Menunggu</option>
        <option value="diproses" {{ request('status')=='diproses' ? 'selected' : '' }}>Diproses</option>
        <option value="selesai"  {{ request('status')=='selesai'  ? 'selected' : '' }}>Selesai</option>
    </select>
    <select name="service" onchange="this.form.submit()">
        <option value="">Semua Layanan</option>
        @foreach($services as $svc)
        <option value="{{ $svc->id }}" {{ request('service')==$svc->id ? 'selected' : '' }}>{{ $svc->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
</form>

{{-- Ticket Grid --}}
<div class="ticket-grid">
    @forelse($tickets as $ticket)
    <div class="ticket-card {{ $ticket->status }}">
        <div class="ticket-meta">
            <span class="ticket-id">{{ $ticket->code }}</span>
            <span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span>
        </div>
        <div class="ticket-title">
            @if($ticket->anonymous && !auth()->user()->isSiswa())
                <i class="fas fa-user-secret" style="color:var(--muted);margin-right:4px"></i>
            @endif
            {{ $ticket->title }}
        </div>
        <div class="ticket-desc">{{ $ticket->description }}</div>
        <div class="ticket-footer">
            <div class="ticket-guru">
                @if($ticket->teacher)
                <div class="ticket-guru-avatar">{{ $ticket->teacher->avatar_initials }}</div>
                <div class="ticket-guru-name">{{ $ticket->teacher->user->name }}</div>
                @else
                <span class="text-muted" style="font-size:12px"><i class="fas fa-user-clock"></i> Belum ditugaskan</span>
                @endif
            </div>
            <div class="action-btns">
                @if($ticket->status !== 'selesai')
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> {{ auth()->user()->isSiswa() ? 'Chat' : 'Balas' }}</a>
                @else
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Lihat</a>
                @endif
                @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" style="display:inline" onsubmit="return confirm('Hapus tiket ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state" style="grid-column:1/-1">
        <i class="fas fa-inbox"></i>
        <p>Belum ada tiket konsultasi</p>
        @if(auth()->user()->isSiswa())
        <a href="{{ route('tickets.create') }}" class="btn btn-primary" style="margin-top:12px"><i class="fas fa-plus"></i> Ajukan Sekarang</a>
        @endif
    </div>
    @endforelse
</div>

{{ $tickets->links() }}

{{-- Modal Tambah Tiket (Admin) --}}
@if(auth()->user()->isAdmin())
<div class="modal-overlay" id="modal-ticket">
    <div class="modal">
        <div class="modal-header"><h3>Buat Tiket Baru</h3><button class="modal-close" onclick="closeModal('modal-ticket')">✕</button></div>
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf
            <div class="field-group"><label>Jenis Layanan</label>
                <select name="service_id" required>
                    @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group"><label>Assign Guru BK</label>
                <select name="teacher_id">
                    <option value="">Pilih Guru BK...</option>
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->user->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group"><label>Judul</label><input type="text" name="title" required placeholder="Topik masalah"></div>
            <div class="field-group"><label>Deskripsi</label><textarea name="description" required placeholder="Jelaskan masalah..."></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-ticket')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Buat Tiket</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
