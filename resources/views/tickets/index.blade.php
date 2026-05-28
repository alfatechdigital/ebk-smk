@extends('layouts.app')
@section('title', 'Tiket Konsultasi')
@section('page-title', auth()->user()->isSiswa() ? 'Konsultasi Saya' : 'Manajemen Tiket')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        @if(auth()->user()->isSiswa())
            <h2>Konsultasi Saya</h2>
            <p>Kelola tiket konsultasi kamu</p>
        @else
            <h2>Manajemen Tiket</h2>
            <p>Daftar semua tiket konsultasi siswa</p>
        @endif
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

<!-- Filter -->
<form class="filter-bar" method="GET">
    <input type="text" name="search" placeholder="Cari tiket, nama siswa..." value="{{ request('search') }}">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="menunggu" {{ request('status')=='menunggu'?'selected':'' }}>Menunggu</option>
        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
        <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
    </select>
    <select name="service" onchange="this.form.submit()">
        <option value="">Semua Layanan</option>
        @foreach($services as $s)
            <option value="{{ $s->id }}" {{ request('service')==$s->id?'selected':'' }}>{{ $s->name }}</option>
        @endforeach
    </select>
</form>

<!-- Ticket Grid -->
<div class="ticket-grid">
    @forelse ($tickets as $ticket)
    <div class="ticket-card {{ $ticket->status }}">
        <div class="ticket-meta">
            <span class="ticket-id">{{ $ticket->code }}</span>
            <span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span>
        </div>
        <div class="ticket-title">{{ $ticket->title }}</div>
        <div class="ticket-desc">{{ $ticket->description }}</div>
        <div class="ticket-footer">
            <div class="ticket-guru">
                @if($ticket->teacher)
                <div class="ticket-guru-avatar">{{ $ticket->teacher->avatar_initials }}</div>
                <div class="ticket-guru-name">{{ $ticket->teacher->user->name ?? '-' }}</div>
                @else
                <span class="text-muted" style="font-size:12px">Belum ditugaskan</span>
                @endif
            </div>
            @if($ticket->status !== 'selesai')
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> {{ $ticket->status==='menunggu'?'Balas':'Lanjut' }}</a>
            @else
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i> Lihat</a>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state" style="grid-column:1/-1">
        <i class="fas fa-ticket-alt"></i>
        <p>Belum ada tiket konsultasi</p>
        @if(auth()->user()->isSiswa())
            <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-20"><i class="fas fa-plus"></i> Ajukan Konsultasi</a>
        @endif
    </div>
    @endforelse
</div>
{{ $tickets->links() }}
@endsection

@push('modals')
@if(auth()->user()->isAdmin())
<div class="modal-overlay" id="modal-ticket">
    <div class="modal">
        <div class="modal-header"><h3>Buat Tiket Baru</h3><button class="modal-close" onclick="closeModal('modal-ticket')">✕</button></div>
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf
            <div class="field-group">
                <label>Jenis Layanan</label>
                <select name="service_id" required>
                    <option value="">Pilih layanan...</option>
                    @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group">
                <label>Guru BK</label>
                <select name="teacher_id">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->user->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group"><label>Judul</label><input type="text" name="title" placeholder="Judul tiket" required></div>
            <div class="field-group"><label>Deskripsi Masalah</label><textarea name="description" placeholder="Jelaskan masalah..." required></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-ticket')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Buat Tiket</button>
            </div>
        </form>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')})});
</script>
@endpush
