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
        <form method="POST" action="{{ route('profil.update') }}" id="form-profil">
            @csrf @method('PUT')
            
            @if($user->isSiswa())
                {{-- Tampilan Profil Siswa --}}
                <div class="field-group">
                    <label>Nama Lengkap</label>
                    <input type="text" value="{{ $user->name }}" readonly style="background-color: #f3f4f6; cursor: not-allowed; opacity: 0.85;">
                </div>
                <div class="field-group">
                    <label>NIS (Nomor Induk Siswa)</label>
                    <input type="text" value="{{ $user->student?->nis ?? '-' }}" readonly style="background-color: #f3f4f6; cursor: not-allowed; opacity: 0.85;">
                </div>
                <div class="field-group">
                    <label>Kelas</label>
                    <input type="text" value="{{ $user->student?->class?->name ?? '-' }}" readonly style="background-color: #f3f4f6; cursor: not-allowed; opacity: 0.85;">
                </div>
                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" required>
                </div>
                <div class="field-group">
                    <label>Nomor HP</label>
                    <input type="text" name="no_hp" value="{{ $user->student?->no_hp ?? '' }}" placeholder="Nomor HP aktif (misal: 08123456789)">
                </div>
            @else
                {{-- Tampilan Profil Guru BK / Admin --}}
                <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" value="{{ $user->name }}" required></div>
                <div class="field-group"><label>Email</label><input type="email" name="email" value="{{ $user->email }}" required></div>
                @if($user->isGuru())
                    <div class="field-group"><label>NIP</label><input type="text" name="nip" value="{{ $user->teacher?->nip ?? '' }}"></div>
                    <div class="field-group"><label>Spesialisasi</label><input type="text" name="spesialisasi" value="{{ $user->teacher?->spesialisasi ?? '' }}"></div>
                    <div class="field-group"><label>No. WhatsApp</label><input type="text" name="no_whatsapp" value="{{ $user->teacher?->no_whatsapp ?? '' }}"></div>
                @endif
            @endif

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">Ubah Password</div></div>
        <form method="POST" action="{{ route('profil.update') }}" id="form-password">
            @csrf @method('PUT')
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">
            
            <div class="field-group">
                <label>Password Saat Ini</label>
                <div style="position: relative;">
                    <input type="password" name="current_password" id="input-current-password" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePasswordVisibility('input-current-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate); font-size: 14px; padding: 0;">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="field-group">
                <label>Password Baru</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="input-new-password" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePasswordVisibility('input-new-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate); font-size: 14px; padding: 0;">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="field-group">
                <label>Konfirmasi Password Baru</label>
                <div style="position: relative;">
                    <input type="password" name="password_confirmation" id="input-confirm-password" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">
                    <button type="button" onclick="togglePasswordVisibility('input-confirm-password', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--slate); font-size: 14px; padding: 0;">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

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

@push('modals')
<div class="modal-overlay" id="modal-confirm-profile">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3>Simpan Perubahan Profil?</h3>
        <p style="color: #666; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin menyimpan perubahan informasi profil Anda?
        </p>
        <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-profile')">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitProfileForm()"><i class="fas fa-check-circle"></i> Ya, Simpan</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-confirm-password">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3>Ubah Password Anda?</h3>
        <p style="color: #666; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin memperbarui password login Anda saat ini? Anda perlu menggunakan password baru pada sesi masuk berikutnya.
        </p>
        <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-password')">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitPasswordForm()"><i class="fas fa-check-circle"></i> Ya, Ubah</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });

    const formProfil = document.getElementById('form-profil');
    if (formProfil) {
        formProfil.addEventListener('submit', function(e) {
            e.preventDefault();
            openModal('modal-confirm-profile');
        });
    }

    function submitProfileForm() {
        if (formProfil) {
            formProfil.submit();
        }
    }

    const formPassword = document.getElementById('form-password');
    if (formPassword) {
        formPassword.addEventListener('submit', function(e) {
            e.preventDefault();
            openModal('modal-confirm-password');
        });
    }

    function submitPasswordForm() {
        if (formPassword) {
            formPassword.submit();
        }
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'far fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'far fa-eye';
        }
    }
</script>
@endpush
