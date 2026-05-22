@extends('layouts.app')
@section('title', 'Pengaturan Lembaga')
@section('page-title', 'Pengaturan Lembaga')

@section('content')
<div class="page-header">
    <h2>Pengaturan Lembaga</h2>
    <p>Konfigurasi informasi sekolah dan sistem</p>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Informasi Sekolah</div>
        </div>
        <form method="POST" action="{{ route('pengaturan.update') }}">
            @csrf
            <div class="field-group"><label>Nama Sekolah</label><input type="text" name="name" value="{{ $institute->name }}" required></div>
            <div class="field-group"><label>NPSN</label><input type="text" name="npsn" value="{{ $institute->npsn }}"></div>
            <div class="field-group"><label>Alamat</label><textarea name="address">{{ $institute->address }}</textarea></div>
            <div class="form-row">
                <div class="field-group"><label>No. Telepon</label><input type="text" name="phone" value="{{ $institute->phone }}"></div>
                <div class="field-group"><label>Email Sekolah</label><input type="email" name="email" value="{{ $institute->email }}"></div>
            </div>
            <div class="field-group"><label>Kepala Sekolah</label><input type="text" name="kepala_sekolah" value="{{ $institute->kepala_sekolah }}"></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>

    <div>
        <div class="card mb-20">
            <div class="card-header"><div class="card-title">Tahun Ajaran Aktif</div></div>
            <form method="POST" action="{{ route('pengaturan.update') }}">
                @csrf
                <div class="field-group"><label>Tahun Ajaran</label><input type="text" name="tahun_ajaran" value="{{ $institute->tahun_ajaran ?? '2024/2025' }}" placeholder="2024/2025"></div>
                <div class="field-group"><label>Semester</label>
                    <select name="semester">
                        <option value="Ganjil" {{ ($institute->semester ?? '') === 'Ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                        <option value="Genap"  {{ ($institute->semester ?? '') === 'Genap'  ? 'selected' : '' }}>Semester Genap</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary"><i class="fas fa-save"></i> Simpan</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title">Informasi Sistem</div></div>
            <div style="display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--cream-dark)">
                    <span class="text-muted">Versi Aplikasi</span>
                    <span style="font-weight:600">E-BK v1.0.0</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--cream-dark)">
                    <span class="text-muted">Framework</span>
                    <span style="font-weight:600">Laravel 11</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0">
                    <span class="text-muted">Database</span>
                    <span style="font-weight:600">SQLite (WAL)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
