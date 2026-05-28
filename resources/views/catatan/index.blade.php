@extends('layouts.app')
@section('title', 'Catatan Konseling')
@section('page-title', 'Catatan Konseling')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Catatan Konseling</h2>
        <p>Dokumentasi sesi konseling dengan siswa</p>
    </div>
</div>

<form class="filter-bar" method="GET" action="{{ route('catatan.index') }}">
    <input type="month" name="month" value="{{ request('month') }}">
    <select name="student_id" onchange="this.form.submit()">
        <option value="">Semua Siswa</option>
        @foreach($students as $s)
            <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->user->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
    <button type="submit" formaction="{{ route('catatan.rekap') }}" class="btn btn-primary" style="margin-left:auto"><i class="fas fa-file-pdf"></i> Cetak Rekap PDF</button>
</form>

<div class="catatan-grid">
    @forelse ($notes as $note)
    <div class="catatan-item">
        <div class="catatan-date">
            <div class="day">{{ $note->created_at->format('d') }}</div>
            <div class="month">{{ $note->created_at->format('M') }}</div>
        </div>
        <div class="catatan-content">
            <h4>{{ $note->title }}</h4>
            <div class="siswa"><i class="fas fa-user-circle"></i> {{ $note->ticket?->student?->user?->name ?? 'Anonim' }} · {{ $note->ticket?->student?->class?->name ?? '' }}</div>
            <div class="catatan-label">Masalah</div>
            <p>{{ $note->masalah }}</p>
            <div class="catatan-label" style="margin-top:8px">Tindakan</div>
            <p>{{ $note->tindakan }}</p>
        </div>
        <div class="action-btns" style="flex-direction:column;gap:6px">
            <button class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></button>
            <a href="{{ route('catatan.pdf', $note) }}" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf"></i></a>
        </div>
    </div>
    @empty
    <div class="empty-state"><i class="fas fa-notes-medical"></i><p>Belum ada catatan konseling</p></div>
    @endforelse
</div>
{{ $notes->links() }}
@endsection

@push('modals')
<div class="modal-overlay" id="modal-catatan">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Catatan Konseling</h3><button class="modal-close" onclick="closeModal('modal-catatan')">✕</button></div>
        <form method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <div class="field-group">
                <label>Tiket Terkait</label>
                <select name="ticket_id" required>
                    <option value="">Pilih tiket...</option>
                    @php
                        $teacherTickets = \App\Models\Ticket::with('student.user')
                            ->where('teacher_id', auth()->user()->teacher?->id)
                            ->whereIn('status', ['diproses','menunggu'])
                            ->get();
                    @endphp
                    @foreach($teacherTickets as $t)
                        <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->student?->user?->name ?? 'Anonim' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group"><label>Judul</label><input type="text" name="title" placeholder="Judul catatan" required></div>
            <div class="field-group"><label>Masalah / Permasalahan</label><textarea name="masalah" placeholder="Deskripsikan masalah yang dihadapi siswa..." required></textarea></div>
            <div class="field-group"><label>Tindakan yang Dilakukan</label><textarea name="tindakan" placeholder="Tindakan, teknik, atau intervensi..." required></textarea></div>
            <div class="field-group"><label>Kesimpulan (opsional)</label><textarea name="kesimpulan" placeholder="Kesimpulan sesi konseling..."></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-catatan')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')})});
</script>
@endpush
