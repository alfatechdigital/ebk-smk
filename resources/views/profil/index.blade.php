@extends('layouts.app')
@section('title', 'Profil')
@section('page-title', 'Profil Saya')

@section('content')
<div class="profile-hero">
    <div class="profile-hero-avatar">{{ $user->avatar_initials }}</div>
    <div class="profile-hero-info">
        <h2>{{ $user->name }}</h2>
        <p>{{ $user->role_label }}{{ $user->isGuru() && $user->teacher ? ' · ' . ($user->teacher->spesialisasi ?? '') : '' }}{{ $user->isSiswa() && $user->student?->class ? ' · ' . $user->student->class->name : '' }}</p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">Informasi Profil</div></div>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')
            <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" value="{{ $user->name }}" required></div>
            <div class="field-group"><label>Email</label><input type="email" name="email" value="{{ $user->email }}" required></div>
            @if($user->isGuru())
            <div class="field-group"><label>Spesialisasi</label><input type="text" name="spesialisasi" value="{{ $user->teacher?->spesialisasi ?? '' }}"></div>
            <div class="field-group"><label>No. WhatsApp</label><input type="text" name="no_whatsapp" value="{{ $user->teacher?->no_whatsapp ?? '' }}"></div>
            @endif
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">Ubah Password</div></div>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">
            <div class="field-group"><label>Password Saat Ini</label><input type="password" name="current_password" required></div>
            <div class="field-group"><label>Password Baru</label><input type="password" name="password" required></div>
            <div class="field-group"><label>Konfirmasi Password Baru</label><input type="password" name="password_confirmation" required></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Ubah Password</button>
        </form>
    </div>
</div>

@if($user->isGuru() || $user->isAdmin())
<div class="card mt-20">
    <div class="card-header"><div class="card-title">Detail Profil</div></div>
    <div class="profile-info-grid">
        <div class="profile-info-item"><label>NIP</label><p>{{ $user->teacher?->nip ?? '-' }}</p></div>
        <div class="profile-info-item"><label>Email</label><p>{{ $user->email }}</p></div>
        <div class="profile-info-item"><label>No. WhatsApp</label><p>{{ $user->teacher?->no_whatsapp ?? '-' }}</p></div>
        <div class="profile-info-item"><label>Spesialisasi</label><p>{{ $user->teacher?->spesialisasi ?? '-' }}</p></div>
    </div>
</div>
@endif
@endsection
