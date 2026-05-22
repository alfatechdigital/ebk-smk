@extends('layouts.app')
@section('title', 'Kategori Layanan')
@section('page-title', 'Kategori Layanan')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Kategori Layanan</h2>
        <p>Kelola jenis layanan konseling yang tersedia</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-kategori')">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

<div class="grid-3">
    @forelse($services as $svc)
    <div class="card" style="border-top:4px solid {{ $svc->color }}">
        <div style="width:48px;height:48px;background:{{ $svc->color }}22;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:{{ $svc->color }};margin-bottom:14px">
            <i class="{{ $svc->icon }}"></i>
        </div>
        <h4 style="font-weight:700;font-size:15px;margin-bottom:6px">{{ $svc->name }}</h4>
        <p class="text-muted" style="margin-bottom:12px;line-height:1.5">{{ $svc->description }}</p>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="badge {{ $svc->is_active ? 'badge-success' : 'badge-danger' }}">{{ $svc->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            <div class="action-btns">
                <button class="btn btn-secondary btn-sm" onclick="editService({{ $svc->id }}, '{{ addslashes($svc->name) }}', '{{ addslashes($svc->description) }}', '{{ $svc->icon }}', '{{ $svc->color }}', {{ $svc->is_active ? 'true' : 'false' }})">
                    <i class="fas fa-edit"></i>
                </button>
                <form method="POST" action="{{ route('kategori.destroy', $svc) }}" onsubmit="return confirm('Hapus kategori ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state" style="grid-column:1/-1">
        <i class="fas fa-tags"></i>
        <p>Belum ada kategori layanan</p>
    </div>
    @endforelse
</div>

{{-- Modal Tambah --}}
<div class="modal-overlay" id="modal-kategori">
    <div class="modal">
        <div class="modal-header"><h3 id="kat-title">Tambah Kategori Layanan</h3><button class="modal-close" onclick="closeModal('modal-kategori')">✕</button></div>
        <form method="POST" id="kat-form" action="{{ route('kategori.store') }}">
            @csrf
            <span id="kat-method"></span>
            <div class="field-group"><label>Nama Kategori</label><input type="text" name="name" id="kat-name" required placeholder="Nama layanan konseling"></div>
            <div class="field-group"><label>Deskripsi</label><textarea name="description" id="kat-desc" placeholder="Penjelasan singkat..."></textarea></div>
            <div class="form-row">
                <div class="field-group"><label>Ikon (Font Awesome)</label><input type="text" name="icon" id="kat-icon" placeholder="fas fa-user"></div>
                <div class="field-group"><label>Warna</label><input type="color" name="color" id="kat-color" value="#0d7c66" style="height:42px;cursor:pointer"></div>
            </div>
            <div class="field-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" id="kat-active" checked style="accent-color:var(--teal);width:16px;height:16px">
                    Aktifkan layanan ini
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-kategori')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editService(id, name, desc, icon, color, active) {
    document.getElementById('kat-title').textContent = 'Edit Kategori';
    document.getElementById('kat-name').value   = name;
    document.getElementById('kat-desc').value   = desc;
    document.getElementById('kat-icon').value   = icon;
    document.getElementById('kat-color').value  = color;
    document.getElementById('kat-active').checked = active;
    document.getElementById('kat-form').action  = `/kategori/${id}`;
    document.getElementById('kat-method').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('modal-kategori');
}
</script>
@endpush
