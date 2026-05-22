@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', auth()->user()->isGuru() ? 'Profil Guru BK' : 'Profil Saya')

@section('content')
<div class="profile-hero">
    <div class="profile-hero-avatar">{{ $user->avatar_initials }}</div>
    <div class="profile-hero-info">
        <h2>{{ $user->name }}</h2>
        <p>{{ $user->role_label }} @if($user->isGuru() && $user->teacher?->spesialisasi)· {{ $user->teacher->spesialisasi }}@endif</p>
        <p style="margin-top:8px;opacity:.8;font-size:13px">{{ $user->email }}</p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">Edit Profil</div></div>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')
            <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" value="{{ $user->name }}" required></div>
            <div class="field-group"><label>Email</label><input type="email" name="email" value="{{ $user->email }}" required></div>
            @if($user->isGuru())
            <div class="field-group"><label>No. WhatsApp</label><input type="text" name="no_whatsapp" value="{{ $user->teacher?->no_whatsapp }}" placeholder="08xx..."></div>
            <div class="field-group"><label>Spesialisasi</label><input type="text" name="spesialisasi" value="{{ $user->teacher?->spesialisasi }}" placeholder="Konseling Individual & Karir"></div>
            @endif
            <hr style="border:none;border-top:1px solid var(--cream-dark);margin:16px 0">
            <p style="font-size:13px;font-weight:700;color:var(--slate);margin-bottom:10px">Ubah Password</p>
            <div class="field-group"><label>Password Saat Ini</label><input type="password" name="current_password" placeholder="Password lama"></div>
            <div class="form-row">
                <div class="field-group"><label>Password Baru</label><input type="password" name="password" placeholder="Password baru"></div>
                <div class="field-group"><label>Konfirmasi Password</label><input type="password" name="password_confirmation" placeholder="Ulangi password"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>

    <div>
        <div class="card mb-20">
            <div class="card-header"><div class="card-title">Informasi Akun</div></div>
            <div class="profile-info-grid">
                <div class="profile-info-item"><label>Role</label><p>{{ $user->role_label }}</p></div>
                <div class="profile-info-item"><label>Status</label><p>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</p></div>
                @if($user->isSiswa() && $user->student)
                <div class="profile-info-item"><label>NIS</label><p>{{ $user->student->nis ?? '—' }}</p></div>
                <div class="profile-info-item"><label>Kelas</label><p>{{ $user->student->class?->name ?? '—' }}</p></div>
                @endif
                @if($user->isGuru() && $user->teacher)
                <div class="profile-info-item"><label>NIP</label><p>{{ $user->teacher->nip ?? '—' }}</p></div>
                <div class="profile-info-item"><label>WhatsApp</label><p>{{ $user->teacher->no_whatsapp ?? '—' }}</p></div>
                @endif
                <div class="profile-info-item"><label>Bergabung</label><p>{{ $user->created_at->isoFormat('D MMMM YYYY') }}</p></div>
            </div>
        </div>

        @if($user->isGuru())
        @php $teacher = $user->teacher; @endphp
        <div class="card">
            <div class="card-header"><div class="card-title">Statistik Konseling</div></div>
            <div style="display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--cream-dark)">
                    <span class="text-muted">Total Tiket Ditangani</span>
                    <span style="font-weight:700;color:var(--teal)">{{ $teacher?->tickets->count() ?? 0 }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--cream-dark)">
                    <span class="text-muted">Tiket Selesai</span>
                    <span style="font-weight:700;color:var(--teal)">{{ $teacher?->tickets->where('status','selesai')->count() ?? 0 }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:10px 0">
                    <span class="text-muted">Catatan Dibuat</span>
                    <span style="font-weight:700;color:var(--teal)">{{ $teacher?->counselingNotes->count() ?? 0 }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
