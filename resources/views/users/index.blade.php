@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Manajemen Pengguna</h2>
        <p>Kelola data semua pengguna sistem</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-user')">
        <i class="fas fa-plus"></i> Tambah User
    </button>
</div>

<form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Cari nama, NIS, email..." value="{{ request('search') }}">
    <select name="role" onchange="this.form.submit()">
        <option value="">Semua Role</option>
        <option value="superadmin" {{ request('role')=='superadmin' ? 'selected' : '' }}>Super Admin</option>
        <option value="admin"      {{ request('role')=='admin'      ? 'selected' : '' }}>Admin</option>
        <option value="guru"       {{ request('role')=='guru'       ? 'selected' : '' }}>Guru BK</option>
        <option value="siswa"      {{ request('role')=='siswa'      ? 'selected' : '' }}>Siswa</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>No</th><th>Nama</th><th>Email</th><th>Role</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td>{{ $users->firstItem() + $i }}</td>
                    <td><b>{{ $user->name }}</b></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @php $badgeMap = ['superadmin'=>'badge-danger','admin'=>'badge-danger','guru'=>'badge-success','siswa'=>'badge-info']; @endphp
                        <span class="badge {{ $badgeMap[$user->role] ?? 'badge-info' }}">{{ $user->role_label }}</span>
                    </td>
                    <td>{{ $user->student?->class?->name ?? '—' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="action-btns">
                        <button class="btn btn-secondary btn-sm" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if(auth()->id() !== $user->id)
                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--muted)">Tidak ada pengguna ditemukan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $users->withQueryString()->links() }}

{{-- Modal Tambah/Edit User --}}
<div class="modal-overlay" id="modal-user">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal-user-title">Tambah User</h3>
            <button class="modal-close" onclick="closeModal('modal-user')">✕</button>
        </div>
        <form method="POST" action="{{ route('users.store') }}" id="user-form">
            @csrf
            <span id="method-field"></span>
            <div class="form-row">
                <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" id="u-name" placeholder="Nama lengkap" required></div>
                <div class="field-group"><label>NIS / NIP</label><input type="text" name="nis_nip" id="u-nisnip" placeholder="NIS atau NIP"></div>
            </div>
            <div class="form-row">
                <div class="field-group">
                    <label>Role</label>
                    <select name="role" id="u-role" required>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru BK</option>
                        <option value="admin">Admin</option>
                        @if(auth()->user()->isSuperAdmin())<option value="superadmin">Super Admin</option>@endif
                    </select>
                </div>
                <div class="field-group">
                    <label>Kelas</label>
                    <select name="class_id">
                        <option value="">— Pilih kelas —</option>
                        @foreach($classes as $cls)<option value="{{ $cls->id }}">{{ $cls->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="field-group"><label>Email</label><input type="email" name="email" id="u-email" placeholder="email@example.com" required></div>
                <div class="field-group"><label>Password <span id="pass-hint" style="font-weight:400;color:var(--muted)">(wajib)</span></label><input type="password" name="password" id="u-password" placeholder="Password"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-user')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editUser(id, name, email, role) {
    document.getElementById('modal-user-title').textContent = 'Edit User';
    document.getElementById('u-name').value  = name;
    document.getElementById('u-email').value = email;
    document.getElementById('u-role').value  = role;
    document.getElementById('u-password').required = false;
    document.getElementById('pass-hint').textContent = '(kosongkan jika tidak diubah)';
    const form = document.getElementById('user-form');
    form.action = `/users/${id}`;
    document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('modal-user');
}
</script>
@endpush
