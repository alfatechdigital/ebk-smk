@extends('layouts.app')
@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')

@push('styles')
<style>
    .action-dropdown-container {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        position: relative;
    }
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: #ffffff;
        min-width: 130px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 1000;
        padding: 6px 0;
    }
    .dropdown-menu.show {
        display: block;
    }
    .dropdown-menu button {
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 8px 16px;
        font-size: 0.85rem;
        color: #334155;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }
    .dropdown-menu button:hover {
        background: #f1f5f9;
    }
    .dropdown-menu button.delete-btn {
        color: var(--danger);
    }
    .dropdown-menu button.delete-btn:hover {
        background: #fef2f2;
    }
</style>
@endpush

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
                    <th>NIS</th>
                    <th>Nama</th>
                    <th style="text-align: center;">JK</th>
                    <th>Kelas</th>
                    <th>No. HP</th>
                    <th>Email</th>
                    <th style="text-align: center;">Total Konsultasi</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $i => $s)
                <tr>
                    <td style="text-align: center;">{{ $students->firstItem() + $i }}</td>
                    <td>{{ $s->nis ?? '-' }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td style="text-align: center;">{{ $s->user->jenis_kelamin ?? '-' }}</td>
                    <td>{{ $s->class?->name ?? '-' }}</td>
                    <td>{{ $s->user->no_hp ?? $s->no_hp ?? '-' }}</td>
                    <td>{{ $s->user->email }}</td>
                    <td style="text-align: center;">{{ $s->tickets->count() }}</td>
                    <td style="text-align: center;"><span class="badge {{ $s->user->is_active?'badge-success':'badge-danger' }}">{{ $s->user->is_active?'Aktif':'Nonaktif' }}</span></td>
                    <td style="text-align: center;">
                        <div class="action-dropdown-container">
                            <button class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Detail Siswa" onclick="openDetailSiswaModal({{ json_encode([
                                'name' => $s->user->name,
                                'nis' => $s->nis ?? '-',
                                'class' => $s->class?->name ?? '-',
                                'gender' => $s->user->jenis_kelamin === 'L' ? 'Laki-laki' : ($s->user->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                                'phone' => $s->user->no_hp ?? $s->no_hp ?? '-',
                                'email' => $s->user->email,
                                'status' => $s->user->is_active ? 'Aktif' : 'Nonaktif',
                                'total_tickets' => $s->tickets->count(),
                                'active_tickets' => $s->tickets->where('status', '!=', 'resolved')->count(),
                                'resolved_tickets' => $s->tickets->where('status', 'resolved')->count()
                            ]) }})"><i class="fas fa-eye"></i> Detail</button>

                            <div class="dropdown">
                                <button class="btn btn-secondary btn-sm" onclick="toggleActionDropdown(event, this)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <button type="button" onclick="openEditSiswaModal({{ json_encode([
                                        'id' => $s->id,
                                        'nis' => $s->nis,
                                        'no_hp' => $s->user->no_hp ?? $s->no_hp,
                                        'class_id' => $s->class_id,
                                        'user' => [
                                            'name' => $s->user->name,
                                            'email' => $s->user->email,
                                            'jenis_kelamin' => $s->user->jenis_kelamin
                                        ]
                                    ]) }})">
                                        <i class="fas fa-edit" style="color: #f59e0b;"></i> Edit
                                    </button>
                                    <button type="button" class="delete-btn" onclick="openDeleteSiswaModal({{ $s->id }})">
                                        <i class="fas fa-trash" style="color: var(--danger);"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-muted" style="text-align:center">Belum ada data siswa</td></tr>
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
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin">
                    <option value="">— Pilih Jenis Kelamin —</option>
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
                @error('jenis_kelamin')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
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
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" id="edit-gender">
                    <option value="">— Pilih Jenis Kelamin —</option>
                    <option value="L">Laki-laki (L)</option>
                    <option value="P">Perempuan (P)</option>
                </select>
                @error('jenis_kelamin')<span style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
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

{{-- Modal Konfirmasi Edit Siswa --}}
<div class="modal-overlay" id="modal-confirm-edit-siswa">
    <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
        <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Simpan Perubahan Data Siswa?</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin menyimpan perubahan pada data siswa ini?
        </p>
        <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-edit-siswa')" style="margin: 0;">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitEditSiswaForm()" style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya, Simpan</button>
        </div>
    </div>
</div>

{{-- Modal Detail Siswa --}}
<div class="modal-overlay" id="modal-detail-siswa">
    <div class="modal" style="max-width: 500px; width: 95%;">
        <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
            <h3>Detail Profil Siswa</h3>
            <button class="modal-close" onclick="closeModal('modal-detail-siswa')">✕</button>
        </div>
        <div class="modal-body" style="padding-top: 20px;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 20px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #f0fdfa; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--teal); font-weight: bold;" id="detail-initial">
                    S
                </div>
                <div>
                    <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;" id="detail-name">Rafi Ahmad</h4>
                    <span style="font-size: 0.85rem; color: #64748b;" id="detail-nis-badge">NIS: 2024003</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 25px;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Kelas</span>
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-class">XI IPA 2</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Jenis Kelamin</span>
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-gender">Laki-laki</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">No. WhatsApp / HP</span>
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-phone">08122334455</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Email</span>
                    <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-email">siswa@ebk.id</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Status Akun</span>
                    <span style="width: fit-content;" id="detail-status">Aktif</span>
                </div>
            </div>

            <div style="background: #f8fafc; border-radius: 8px; padding: 15px; border: 1px solid #f1f5f9;">
                <h5 style="margin: 0 0 10px 0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700;">Statistik Konsultasi</h5>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; gap: 10px;">
                    <div style="background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 1.25rem; font-weight: 700; color: #334155;" id="detail-total-tickets">0</span>
                        <span style="font-size: 0.75rem; color: #94a3b8;">Total Tiket</span>
                    </div>
                    <div style="background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 1.25rem; font-weight: 700; color: var(--teal);" id="detail-active-tickets">0</span>
                        <span style="font-size: 0.75rem; color: #94a3b8;">Berjalan</span>
                    </div>
                    <div style="background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 1.25rem; font-weight: 700; color: #059669;" id="detail-resolved-tickets">0</span>
                        <span style="font-size: 0.75rem; color: #94a3b8;">Selesai</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-siswa')" style="margin: 0; width: 100%; display: flex; justify-content: center; align-items: center; text-align: center;">Tutup</button>
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
        const editForm = document.getElementById('form-edit-siswa');
        if (editForm) {
            editForm.addEventListener('submit', function(event) {
                event.preventDefault();
                openModal('modal-confirm-edit-siswa');
            });
        }
    });

    window.submitEditSiswaForm = function() {
        const editForm = document.getElementById('form-edit-siswa');
        if (editForm) {
            editForm.submit();
        }
    };
    
    function openEditSiswaModal(student) {
        document.getElementById('form-edit-siswa').action = '/data-siswa/' + student.id;
        document.getElementById('edit-name').value = student.user.name;
        document.getElementById('edit-nis').value = student.nis;
        document.getElementById('edit-no-hp').value = student.no_hp || '';
        document.getElementById('edit-gender').value = student.user.jenis_kelamin || '';
        document.getElementById('edit-email').value = student.user.email;
        document.getElementById('edit-class-id').value = student.class_id;
        openModal('modal-edit-siswa');
    }

    function openDeleteSiswaModal(id) {
        document.getElementById('form-delete-siswa').action = '/data-siswa/' + id;
        openModal('modal-delete-siswa');
    }

    function openDetailSiswaModal(student) {
        document.getElementById('detail-initial').textContent = student.name.charAt(0).toUpperCase();
        document.getElementById('detail-name').textContent = student.name;
        document.getElementById('detail-nis-badge').textContent = 'NIS: ' + student.nis;
        document.getElementById('detail-class').textContent = student.class;
        document.getElementById('detail-gender').textContent = student.gender;
        document.getElementById('detail-phone').textContent = student.phone;
        document.getElementById('detail-email').textContent = student.email;
        document.getElementById('detail-status').textContent = student.status;
        
        const statusSpan = document.getElementById('detail-status');
        statusSpan.className = '';
        statusSpan.classList.add('badge');
        if (student.status === 'Aktif') {
            statusSpan.classList.add('badge-success');
        } else {
            statusSpan.classList.add('badge-danger');
        }
        
        document.getElementById('detail-total-tickets').textContent = student.total_tickets;
        document.getElementById('detail-active-tickets').textContent = student.active_tickets;
        document.getElementById('detail-resolved-tickets').textContent = student.resolved_tickets;
        
        openModal('modal-detail-siswa');
    }

    window.toggleActionDropdown = function(event, button) {
        event.stopPropagation();
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== button.nextElementSibling) {
                menu.classList.remove('show');
            }
        });
        button.nextElementSibling.classList.toggle('show');
    };

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });

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
