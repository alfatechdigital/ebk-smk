@extends('layouts.app')
@section('title', 'Catatan Konseling')
@section('page-title', 'Catatan Konseling')

@section('content')
<div class="page-header">
    <h2>Catatan Konseling</h2>
    <p>Rekam tindakan dan perkembangan konseling siswa</p>
</div>

<div class="catatan-grid">
    @forelse($notes as $note)
    <div class="catatan-item">
        <div class="catatan-date">
            <div class="day">{{ $note->created_at->format('d') }}</div>
            <div class="month">{{ $note->created_at->isoFormat('MMM') }}</div>
        </div>
        <div class="catatan-content">
            <h4>{{ $note->title }}</h4>
            <div class="siswa">
                <i class="fas fa-user-circle"></i>
                {{ $note->ticket?->student?->user?->name ?? 'Anonim' }}
                @if($note->ticket?->student?->class)· {{ $note->ticket->student->class->name }}@endif
            </div>
            <div class="catatan-label">Masalah</div>
            <p>{{ Str::limit($note->masalah, 150) }}</p>
            <div class="catatan-label" style="margin-top:8px">Tindakan</div>
            <p>{{ Str::limit($note->tindakan, 150) }}</p>
        </div>
        <div class="action-btns" style="flex-direction:column;gap:6px">
            <a href="{{ route('catatan.pdf', $note) }}" class="btn btn-secondary btn-sm" title="Unduh PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
            <button class="btn btn-secondary btn-sm" onclick="editNote({{ $note->id }})">
                <i class="fas fa-edit"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-notes-medical"></i>
        <p>Belum ada catatan konseling</p>
    </div>
    @endforelse
</div>
{{ $notes->links() }}

{{-- Modal Edit --}}
<div class="modal-overlay" id="modal-edit-catatan">
    <div class="modal">
        <div class="modal-header"><h3>Edit Catatan</h3><button class="modal-close" onclick="closeModal('modal-edit-catatan')">✕</button></div>
        <form method="POST" id="note-edit-form">
            @csrf @method('PUT')
            <div class="field-group"><label>Judul</label><input type="text" name="title" id="e-title" required></div>
            <div class="field-group"><label>Masalah</label><textarea name="masalah" id="e-masalah" required></textarea></div>
            <div class="field-group"><label>Tindakan</label><textarea name="tindakan" id="e-tindakan" required></textarea></div>
            <div class="field-group"><label>Kesimpulan</label><textarea name="kesimpulan" id="e-kesimpulan"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-catatan')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const notes = @json($notes->items());
function editNote(id) {
    const note = notes.find(n => n.id === id);
    if (!note) return;
    document.getElementById('e-title').value     = note.title;
    document.getElementById('e-masalah').value   = note.masalah;
    document.getElementById('e-tindakan').value  = note.tindakan;
    document.getElementById('e-kesimpulan').value= note.kesimpulan || '';
    document.getElementById('note-edit-form').action = `/catatan/${id}`;
    openModal('modal-edit-catatan');
}
</script>
@endpush
