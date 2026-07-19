@extends('layouts.app')
@section('title', 'Pengaturan Lembaga')
@section('page-title', 'Pengaturan Lembaga')

@section('content')
<div class="page-header">
    <h2>Pengaturan Lembaga</h2>
    <p>Konfigurasi informasi sekolah dan sistem</p>
</div>

<form method="POST" action="{{ route('pengaturan.update') }}">
@csrf
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">Informasi Sekolah</div><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button></div>
        <div class="field-group"><label>Nama Sekolah</label><input type="text" name="name" value="{{ $institute->name ?? '' }}" required></div>
        <div class="field-group"><label>NPSN</label><input type="text" name="npsn" value="{{ $institute->npsn ?? '' }}"></div>
        <div class="field-group"><label>Alamat</label><textarea name="address">{{ $institute->address ?? '' }}</textarea></div>
        <div class="form-row">
            <div class="field-group"><label>No. Telepon</label><input type="text" name="phone" value="{{ $institute->phone ?? '' }}"></div>
            <div class="field-group"><label>Email Sekolah</label><input type="email" name="email" value="{{ $institute->email ?? '' }}"></div>
        </div>
        <div class="field-group"><label>Kepala Sekolah</label><input type="text" name="kepala_sekolah" value="{{ $institute->kepala_sekolah ?? '' }}"></div>
    </div>
    <div>
        <div class="card mb-20">
            <div class="card-header"><div class="card-title">Tahun Ajaran Aktif</div></div>
            <div class="field-group"><label>Tahun Ajaran</label><input type="text" name="tahun_ajaran" value="{{ $institute->tahun_ajaran ?? '2024/2025' }}"></div>
            <div class="field-group"><label>Semester</label><select name="semester"><option value="Ganjil" {{ ($institute->semester??'')==='Ganjil'?'selected':'' }}>Semester Ganjil</option><option value="Genap" {{ ($institute->semester??'')==='Genap'?'selected':'' }}>Semester Genap</option></select></div>
        </div>
    </div>
</div>
</form>

@push('modals')
{{-- Modal Konfirmasi Simpan Pengaturan --}}
<div class="modal-overlay" id="modal-confirm-pengaturan">
    <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
        <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Simpan Pengaturan?</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin menyimpan perubahan konfigurasi informasi sekolah dan lembaga ini?
        </p>
        <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-pengaturan')" style="margin: 0;">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitPengaturanForm()" style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya, Simpan</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });

    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector('form[action="{{ route("pengaturan.update") }}"]');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                openModal('modal-confirm-pengaturan');
            });
        }
    });

    window.submitPengaturanForm = function() {
        const form = document.querySelector('form[action="{{ route("pengaturan.update") }}"]');
        if (form) {
            form.submit();
        }
    };
</script>
@endpush
@endsection
