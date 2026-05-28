@extends('layouts.app')
@section('title', 'Hak Akses Menu')
@section('page-title', 'Hak Akses Menu')

@section('content')
<div class="page-header">
    <h2>Hak Akses Menu</h2>
    <p>Atur menu yang dapat diakses tiap role</p>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">Konfigurasi Role</div></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach(['Super Admin' => ['desc' => 'Akses penuh semua fitur', 'badge' => 'Full Access', 'badgeCls' => 'badge-danger'],
                       'Admin' => ['desc' => 'Kelola user & pengaturan', 'badge' => '12 Menu', 'badgeCls' => 'badge-info'],
                       'Guru BK' => ['desc' => 'Kelola konseling', 'badge' => '8 Menu', 'badgeCls' => 'badge-success'],
                       'Siswa' => ['desc' => 'Akses layanan konseling', 'badge' => '4 Menu', 'badgeCls' => 'badge-info']] as $role => $info)
            <div style="padding:14px;background:var(--cream);border-radius:var(--radius-sm);display:flex;justify-content:space-between;align-items:center">
                <div><p style="font-weight:700;font-size:14px">{{ $role }}</p><p class="text-muted">{{ $info['desc'] }}</p></div>
                <span class="badge {{ $info['badgeCls'] }}">{{ $info['badge'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">Menu Access — Admin</div><button class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Simpan</button></div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach(['Dashboard','Manajemen User','CRUD Ticket','Kategori Layanan','Pengaturan Lembaga','Update Kelas','Laporan Rekap','Profil'] as $i => $menu)
            <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);background:var(--cream);cursor:pointer;font-size:14px">
                <input type="checkbox" {{ $i < 6 ? 'checked' : '' }} style="accent-color:var(--teal);width:16px;height:16px">{{ $menu }}
            </label>
            @endforeach
        </div>
    </div>
</div>
@endsection
