@extends('layouts.app')
@section('title', 'Kategori Layanan')
@section('page-title', 'Kategori Layanan')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Kategori Layanan</h2>
        <p>Kelola jenis layanan konseling yang tersedia</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-kategori')"><i class="fas fa-plus"></i> Tambah Kategori</button>
</div>

@php $colors = ['#0d7c66','#c8923a','#3d5454','#7c5cbf','#c0392b','#2c3e50']; @endphp

<div class="grid-3">
    @forelse ($services as $i => $s)
    <div class="card" style="border-top:4px solid {{ $colors[$i % count($colors)] }}">
        <div style="width:48px;height:48px;background:{{ $colors[$i % count($colors)] }}20;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:{{ $colors[$i % count($colors)] }};margin-bottom:14px">
            <i class="{{ $s->icon }}"></i>
        </div>
        <h4 style="font-weight:700;font-size:15px;margin-bottom:6px">{{ $s->name }}</h4>
        <p class="text-muted" style="margin-bottom:12px;line-height:1.5">{{ $s->description }}</p>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span class="badge {{ $s->is_active?'badge-success':'badge-danger' }}">{{ $s->is_active?'Aktif':'Nonaktif' }}</span>
            <div class="action-btns">
                <button class="btn btn-secondary btn-sm" onclick="editKategori({{ $s->id }},'{{ $s->name }}','{{ addslashes($s->description) }}','{{ $s->icon }}',{{ $s->is_active?'true':'false' }})"><i class="fas fa-edit"></i></button>
                <form method="POST" action="{{ route('kategori.destroy', $s) }}" style="display:inline" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state" style="grid-column:1/-1"><i class="fas fa-tags"></i><p>Belum ada kategori</p></div>
    @endforelse
</div>
@endsection

@push('modals')
<div class="modal-overlay" id="modal-kategori">
    <div class="modal">
        <div class="modal-header"><h3 id="kat-title">Tambah Kategori Layanan</h3><button class="modal-close" onclick="closeModal('modal-kategori')">✕</button></div>
        <form method="POST" id="kat-form" action="{{ route('kategori.store') }}">
            @csrf
            <input type="hidden" name="_method" id="kat-method" value="POST">
            <div class="field-group"><label>Nama Kategori</label><input type="text" name="name" id="kat-name" placeholder="Nama layanan konseling" required></div>
            <div class="field-group"><label>Deskripsi</label><textarea name="description" id="kat-desc" placeholder="Penjelasan singkat tentang layanan ini..."></textarea></div>
            <div class="form-row">
                <div class="field-group"><label>Ikon (Font Awesome)</label><input type="text" name="icon" id="kat-icon" placeholder="fas fa-user"></div>
                <div class="field-group"><label>Status</label><select name="is_active" id="kat-active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-kategori')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
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
function editKategori(id,name,desc,icon,active){
    document.getElementById('kat-title').textContent='Edit Kategori';
    document.getElementById('kat-form').action='/kategori/'+id;
    document.getElementById('kat-method').value='PUT';
    document.getElementById('kat-name').value=name;
    document.getElementById('kat-desc').value=desc;
    document.getElementById('kat-icon').value=icon;
    document.getElementById('kat-active').value=active?'1':'0';
    openModal('modal-kategori');
}
</script>
@endpush
