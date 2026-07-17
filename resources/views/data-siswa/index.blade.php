@extends('layouts.app')
@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Data Siswa</h2>
        <p>Daftar seluruh siswa yang terdaftar</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-tambah-siswa')"><i class="fas fa-plus"></i> Tambah Siswa</button>
</div>

<form class="filter-bar" method="GET">
    <input type="text" name="search" id="search-input" placeholder="Cari nama siswa..." value="{{ request('search') }}" autocomplete="off">
    <select name="class_id" onchange="this.form.submit()">
        <option value="">Semua Kelas</option>
        @foreach($classes as $c)<option value="{{ $c->id }}" {{ request('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
    </select>
    <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 13px; color: var(--slate); font-weight: 500;">Tampilkan:</span>
        <select name="per_page" onchange="this.form.submit()" style="width: auto; padding: 6px 12px;">
            @foreach([5, 10, 25, 50, 100] as $p)
                <option value="{{ $p }}" {{ request('per_page', 25) == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">No</th>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>No. HP</th>
                    <th style="text-align: center;">Total Konsultasi</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $i => $s)
                <tr>
                    <td style="text-align: center;">{{ $students->firstItem() + $i }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->nis ?? '-' }}</td>
                    <td>{{ $s->class?->name ?? '-' }}</td>
                    <td>{{ $s->no_hp ?? '-' }}</td>
                    <td style="text-align: center;">{{ $s->tickets->count() }}</td>
                    <td style="text-align: center;"><span class="badge {{ $s->user->is_active?'badge-success':'badge-danger' }}">{{ $s->user->is_active?'Aktif':'Nonaktif' }}</span></td>
                    <td style="text-align: center;">
                        <div style="display: inline-flex; gap: 6px; justify-content: center; align-items: center;">
                            <button class="btn btn-secondary btn-sm" title="Edit Siswa" onclick="openEditSiswaModal({{ json_encode([
                                'id' => $s->id,
                                'nis' => $s->nis,
                                'no_hp' => $s->no_hp,
                                'class_id' => $s->class_id,
                                'user' => [
                                    'name' => $s->user->name,
                                    'email' => $s->user->email
                                ]
                            ]) }})"><i class="fas fa-edit"></i></button>
                            
                            <button class="btn btn-danger btn-sm" title="Hapus Siswa" onclick="openDeleteSiswaModal({{ $s->id }})"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-muted" style="text-align:center">Belum ada data siswa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $students->links('vendor.pagination.custom') }}
</div>
@endsection

@push('modals')
{{-- Modal Tambah Siswa --}}
<div class="modal-overlay" id="modal-tambah-siswa">
    <div class="modal" style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Tambah Siswa Baru</h3>
            <button class="modal-close" onclick="closeModal('modal-tambah-siswa')">✕</button>
        </div>
        <form method="POST" action="{{ route('data-siswa.store') }}">
            @csrf
            
            <div class="field-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" placeholder="Nama lengkap siswa" required value="{{ old('name') }}">
                @error('name')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>
            
            <div class="field-group">
                <label>NIS (Nomor Induk Siswa)</label>
                <input type="text" name="nis" placeholder="Nomor Induk Siswa" required value="{{ old('nis') }}">
                @error('nis')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Nomor HP</label>
                <input type="text" name="no_hp" placeholder="Nomor HP siswa (misal: 08123456789)" value="{{ old('no_hp') }}">
                @error('no_hp')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Alamat email siswa" required value="{{ old('email') }}">
                @error('email')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password login" required minlength="6">
                @error('password')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Kelas</label>
                <select name="class_id" required>
                    <option value="">Pilih kelas...</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('class_id')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah-siswa')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Siswa --}}
<div class="modal-overlay" id="modal-edit-siswa">
    <div class="modal" style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Edit Data Siswa</h3>
            <button class="modal-close" onclick="closeModal('modal-edit-siswa')">✕</button>
        </div>
        <form method="POST" id="form-edit-siswa">
            @csrf
            @method('PUT')
            
            <div class="field-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" id="edit-name" required>
                @error('name')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>
            
            <div class="field-group">
                <label>NIS (Nomor Induk Siswa)</label>
                <input type="text" name="nis" id="edit-nis" required>
                @error('nis')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Nomor HP</label>
                <input type="text" name="no_hp" id="edit-no-hp">
                @error('no_hp')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Email</label>
                <input type="email" name="email" id="edit-email" required>
                @error('email')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Password (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Password login baru (minimal 6 karakter)">
                @error('password')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="field-group">
                <label>Kelas</label>
                <select name="class_id" id="edit-class-id" required>
                    <option value="">Pilih kelas...</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('class_id')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-siswa')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal-overlay" id="modal-delete-siswa">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Hapus Data Siswa?</h3>
        <p style="color: #666; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin menghapus data siswa ini? Seluruh riwayat tiket konsultasi, pesan chat, dan catatan terkait siswa ini akan terhapus secara permanen.
        </p>
        <form method="POST" id="form-delete-siswa" style="margin-top: 25px;">
            @csrf
            @method('DELETE')
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-siswa')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });
    
    function openEditSiswaModal(student) {
        document.getElementById('form-edit-siswa').action = '/data-siswa/' + student.id;
        document.getElementById('edit-name').value = student.user.name;
        document.getElementById('edit-nis').value = student.nis;
        document.getElementById('edit-no-hp').value = student.no_hp || '';
        document.getElementById('edit-email').value = student.user.email;
        document.getElementById('edit-class-id').value = student.class_id;
        openModal('modal-edit-siswa');
    }

    function openDeleteSiswaModal(id) {
        document.getElementById('form-delete-siswa').action = '/data-siswa/' + id;
        openModal('modal-delete-siswa');
    }

    @if ($errors->any())
        // Auto-open edit modal if there were errors on fields like password/email for edit
        if(window.location.hash === '#edit') {
            openModal('modal-edit-siswa');
        } else {
            openModal('modal-tambah-siswa');
        }
    @endif

    // Real-time search script
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        // Put cursor at the end of the text if it is active
        if (searchInput.value.trim().length > 0) {
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
            searchInput.focus();
        }

        let searchTimeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.form.submit();
            }, 400); // 400ms debounce
        });
    }
</script>
@endpush
