@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

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
    @media (min-width: 768px) {
        .table-wrap {
            overflow: visible !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Manajemen {{ ucfirst(request('role', 'Pengguna')) }}</h2>
            <p>Kelola data {{ request('role') }} sistem secara spesifik.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            @if(request('role') === 'siswa' || !request('role'))
                <button class="btn btn-secondary" onclick="openImportModal()" style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; font-weight: 500;"><i class="fas fa-file-excel" style="color: #10b981; margin-right: 4px;"></i> Import Siswa</button>
            @endif
            <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah User</button>
            @if(request('role') === 'siswa')
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button class="btn btn-secondary" onclick="toggleActionDropdown(event, this)" style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; padding: 9px 14px; margin: 0; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" style="right: 0; left: auto; min-width: 180px;">
                        <button type="button" onclick="openPromoteModalStep1()" style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 13px; color: var(--charcoal); cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-arrow-up" style="color: var(--teal); width: 16px;"></i> Naikkan Kelas
                        </button>
                        <button type="button" onclick="openDeleteGraduatedModalStep1()" style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 13px; color: var(--danger); cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-trash-alt" style="color: var(--danger); width: 16px;"></i> Hapus Siswa Lulus
                        </button>
                    </div>
                </div>
                <form id="form-promote-classes" method="POST" action="{{ route('users.promote-classes') }}" style="display: none;">
                    @csrf
                </form>
                <form id="form-delete-graduated" method="POST" action="{{ route('users.delete-graduated') }}" style="display: none;">
                    @csrf
                </form>
            @endif
        </div>
    </div>

    <form class="filter-bar" method="GET">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="text" name="search" id="search-input" placeholder="Cari nama, identitas, email..."
            value="{{ request('search') }}">

        @if(request('role') === 'siswa')
            <select name="status" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
                <option value="">— Semua Status —</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="lulus" {{ request('status') === 'lulus' ? 'selected' : '' }}>Lulus</option>
            </select>

            <select name="class_id" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
                <option value="">— Semua Kelas —</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <select name="teacher_id" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
                <option value="">— Semua Guru BK —</option>
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user?->name ?? '-' }}</option>
                @endforeach
            </select>
        @endif

        <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13px; color: var(--slate); font-weight: 500;">Tampilkan:</span>
            <select name="per_page" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
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
                    @if(request('role') === 'siswa')
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th style="text-align: center;">JK</th>
                            <th>Kelas</th>
                            <th>Guru BK</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    @else
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th style="text-align: center;">JK</th>
                            <th>NIP</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($users as $i => $u)
                        @if(request('role') === 'siswa')
                            <tr>
                                <td>{{ $users->firstItem() + $i }}</td>
                                <td>{{ $u->student?->nis ?? '-' }}</td>
                                <td><strong>{{ $u->name }}</strong></td>
                                <td style="text-align: center;">{{ $u->jenis_kelamin ?? '-' }}</td>
                                <td>{{ $u->student?->class?->name ?? '-' }}</td>
                                <td>{{ $u->student?->class?->teacher?->user?->name ?? '-' }}</td>
                                <td>
                                    @if($u->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-info">Lulus</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-dropdown-container">
                                        <button class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Detail Siswa" onclick="openDetailUserModal({{ json_encode([
                                            'role' => 'siswa',
                                            'name' => $u->name,
                                            'nis_nip' => $u->student?->nis ?? '-',
                                            'class' => $u->student?->class?->name ?? '-',
                                            'gender' => $u->jenis_kelamin === 'L' ? 'Laki-laki' : ($u->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                                            'phone' => $u->no_hp ?? '-',
                                            'email' => $u->email,
                                            'status' => $u->is_active ? 'Aktif' : 'Lulus',
                                            'guru_bk' => $u->student?->class?->teacher?->user?->name ?? '-',
                                            'classes_managed' => []
                                        ]) }})"><i class="fas fa-eye"></i> Detail</button>

                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm" onclick="toggleActionDropdown(event, this)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button type="button" onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}','{{ $u->student?->nis ?? $u->teacher?->nip ?? '' }}','{{ $u->student?->class_id ?? '' }}', [],'{{ $u->no_hp }}','{{ $u->jenis_kelamin }}')">
                                                    <i class="fas fa-edit" style="color: #f59e0b;"></i> Edit
                                                </button>
                                                <button type="button" class="delete-btn" onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                                    <i class="fas fa-trash" style="color: var(--danger);"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $users->firstItem() + $i }}</td>
                                <td><strong>{{ $u->name }}</strong><br><small class="text-muted">{{ $u->email }}</small></td>
                                <td style="text-align: center;">{{ $u->jenis_kelamin ?? '-' }}</td>
                                <td>{{ $u->student?->nis ?? $u->teacher?->nip ?? '-' }}</td>
                                <td><span
                                        class="badge {{ $u->role === 'guru' ? 'badge-success' : ($u->role === 'siswa' ? 'badge-info' : 'badge-danger') }}">{{ $u->role_label }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-dropdown-container">
                                        <button class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 4px;" title="Detail User" onclick="openDetailUserModal({{ json_encode([
                                            'role' => $u->role,
                                            'name' => $u->name,
                                            'nis_nip' => $u->teacher?->nip ?? '-',
                                            'class' => '-',
                                            'gender' => $u->jenis_kelamin === 'L' ? 'Laki-laki' : ($u->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                                            'phone' => $u->no_hp ?? '-',
                                            'email' => $u->email,
                                            'status' => $u->is_active ? 'Aktif' : 'Nonaktif',
                                            'classes_managed' => $u->teacher ? $u->teacher->classes->pluck('name')->toArray() : []
                                        ]) }})"><i class="fas fa-eye"></i> Detail</button>

                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm" onclick="toggleActionDropdown(event, this)">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <button type="button" onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}','{{ $u->student?->nis ?? $u->teacher?->nip ?? '' }}','{{ $u->student?->class_id ?? '' }}', [],'{{ $u->no_hp }}','{{ $u->jenis_kelamin }}')">
                                                    <i class="fas fa-edit" style="color: #f59e0b;"></i> Edit
                                                </button>
                                                <button type="button" class="delete-btn" onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
                                                    <i class="fas fa-trash" style="color: var(--danger);"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ request('role') === 'siswa' ? 9 : 7 }}" class="text-muted"
                                style="text-align:center">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links('vendor.pagination.custom') }}
    </div>
@endsection

@push('modals')
    <div class="modal-overlay" id="modal-user">
        <div class="modal" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3 id="user-modal-title">Tambah User</h3>
                <button class="modal-close" onclick="closeModal('modal-user')">✕</button>
            </div>
            <form method="POST" id="user-form" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="_method" id="user-method" value="POST">
                <input type="hidden" name="role" id="user-role" value="{{ request('role', 'siswa') }}">

                <div class="field-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="user-name" required>
                </div>

                <div class="field-group">
                    <label>NIS / NIP</label>
                    <input type="text" name="nis_nip" id="user-nis">
                </div>

                <div class="field-group">
                    <label>No. HP</label>
                    <input type="text" name="no_hp" id="user-nohp" placeholder="Contoh: 08123456789">
                </div>

                <div class="field-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="user-gender">
                        <option value="">— Pilih Jenis Kelamin —</option>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>

                <div id="container-class-siswa-row" style="margin-bottom: 15px;">
                    <div class="field-group" id="container-class-siswa" style="width: 100%; margin-bottom: 0;">
                        <label>Kelas Siswa</label>
                        <select name="class_id" id="user-class-siswa">
                            <option value="">— Pilih Kelas —</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>



                <div class="field-group">
                    <label>Email</label>
                    <input type="email" name="email" id="user-email" required>
                </div>

                <div class="field-group">
                    <label>Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" id="user-password" minlength="6" placeholder="Minimal 6 karakter">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-user')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-import">
        <div class="modal" style="max-width: 500px; width: 95%;">
            <div class="modal-header">
                <h3>Import Data Siswa</h3>
                <button class="modal-close" onclick="closeModal('modal-import')">✕</button>
            </div>
            <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="field-group">
                        <label>File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            style="padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; background: #f8f9fa;">
                    </div>

                </div>
                <a href="/template.xlsx" class="btn btn-primary"><i class="fas fa-download"></i>Unduh Template</a>
                <div
                    style="margin-top: 15px; font-size: 0.9rem; color: #64748b; background: #f0fdf4; padding: 12px; border-radius: 8px; border: 1px solid #bbf7d0;">
                    <p style="margin-bottom: 8px; font-weight: 600; color: #166534;"><i class="fas fa-info-circle"></i>
                        Panduan Format Excel (Baris Pertama)</p>
                    <ul style="margin-left: 20px; list-style-type: disc;">
                        <li><strong>nis</strong> : Nomor Induk Siswa (Wajib untuk pencocokan update/insert)</li>
                        <li><strong>nama</strong> : Nama lengkap siswa</li>
                        <li><strong>jenis_kelamin</strong> : Jenis Kelamin (L atau P)</li>
                        <li><strong>kelas</strong> : Nama kelas (misal: X IPA 1)</li>
                        <li><strong>email</strong> : Alamat email aktif siswa</li>
                        <li><strong>nomor_hp</strong> : Nomor HP atau WhatsApp siswa</li>
                        <li><strong>password</strong> : Password login baru (Opsional, bawaan: password123)</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-import')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Simpan User --}}
    <div class="modal-overlay" id="modal-confirm-user">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Simpan Data User?</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menyimpan perubahan data user ini?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-user')"
                    style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitUserForm()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya,
                    Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus User --}}
    <div class="modal-overlay" id="modal-delete-user">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3>Hapus User?</h3>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menghapus user <strong id="delete-user-name"></strong>? Seluruh data terkait user
                ini akan terhapus secara permanen dari sistem.
            </p>
            <form method="POST" id="form-delete-user" style="margin-top: 25px;">
                @csrf
                @method('DELETE')
                <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 0;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-user')"
                        style="margin: 0;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="margin: 0;"><i class="fas fa-trash"></i> Ya,
                        Hapus</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Detail User --}}
    <div class="modal-overlay" id="modal-detail-user">
        <div class="modal" style="max-width: 500px; width: 95%;">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
                <h3>Detail Profil Pengguna</h3>
                <button class="modal-close" onclick="closeModal('modal-detail-user')">✕</button>
            </div>
            <div class="modal-body" style="padding-top: 20px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 20px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #f0fdfa; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--teal); font-weight: bold;" id="detail-user-initial">
                        U
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;" id="detail-user-name">Nama User</h4>
                            <span class="badge" id="detail-user-role-badge" style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">Siswa</span>
                        </div>
                        <span style="font-size: 0.85rem; color: #64748b;" id="detail-user-nis-nip-badge">NIS: -</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-class-row">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Kelas</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-class">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-gurubk-row">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Guru BK</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-gurubk">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-classes-managed-row">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Kelas Diampu</span>
                        <div id="detail-user-classes-managed-list" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Jenis Kelamin</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-gender">Laki-laki</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">No. WhatsApp / HP</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-phone">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Email</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-email">email@example.com</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Status Akun</span>
                        <span style="width: fit-content;" id="detail-user-status">Aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-user')" style="margin: 0; width: 100%; display: flex; justify-content: center; align-items: center; text-align: center;">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Naikkan Kelas Step 1 --}}
    @if(request('role') === 'siswa')
    <div class="modal-overlay" id="modal-promote-step1">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Kenaikan Kelas (Tahap 1)</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menaikkan seluruh siswa ke tingkat kelas berikutnya? <br><br>
                Siswa kelas <strong>X</strong> akan naik ke kelas <strong>XI</strong>, kelas <strong>XI</strong> naik ke kelas <strong>XII</strong>, dan kelas <strong>XII</strong> akan dinyatakan <strong>Lulus</strong>.
            </p>
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-promote-step1')" style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-gold" onclick="proceedToPromoteStep2()" style="margin: 0;"><i class="fas fa-arrow-right"></i> Lanjut</button>
            </div>
        </div>
    </div>

    {{-- Modal Naikkan Kelas Step 2 --}}
    <div class="modal-overlay" id="modal-promote-step2">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Akhir (Tahap 2)</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                <strong>PERINGATAN KESELAMATAN:</strong> Tindakan ini akan mengubah database secara massal dan <strong>tidak dapat dibatalkan (undo)</strong>. <br><br>
                Pastikan Anda sudah mencadangkan database atau memeriksa data siswa sebelum melanjutkan. Apakah Anda benar-benar yakin ingin memproses kenaikan kelas sekarang?
            </p>
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-promote-step2')" style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitPromoteForm()" style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i class="fas fa-check"></i> Ya, Naikkan Kelas</button>
            </div>
        </div>
    </div>
    @if(session('import_success'))
    <div class="modal-overlay open" id="modal-import-success">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Import Berhasil!</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                {{ session('import_success') }}
            </p>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeModal('modal-import-success')" style="margin: 0; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @if(session('import_error'))
    <div class="modal-overlay open" id="modal-import-error">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Import Gagal</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                {{ session('import_error') }}
            </p>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-danger" onclick="closeModal('modal-import-error')" style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="modal-overlay open" id="modal-validation-errors">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Gagal Menyimpan Data</h3>
            <div style="color: #ef4444; font-size: 0.95rem; margin-top: 15px; text-align: center; padding: 12px; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px;">
                <ul style="margin: 0; padding: 0; list-style: none; line-height: 1.6;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-danger" onclick="closeModal('modal-validation-errors')" style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @if(session('success_modal'))
    <div class="modal-overlay open" id="modal-success-notification">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--success, #10b981); margin-bottom: 15px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Berhasil</h3>
            <p style="color: #64748b; font-size: 0.95rem; margin-top: 10px; line-height: 1.5;">
                {{ session('success_modal') }}
            </p>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeModal('modal-success-notification')" style="margin: 0; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Hapus Siswa Lulus Step 1 --}}
    <div class="modal-overlay" id="modal-delete-graduated-step1">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Hapus Siswa Lulus (Tahap 1)</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menghapus **seluruh** siswa yang berstatus **Lulus** dari database? <br><br>
                Tindakan ini akan menghapus akun login dan riwayat siswa terkait.
            </p>
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-graduated-step1')" style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-danger" onclick="proceedToDeleteGraduatedStep2()" style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i class="fas fa-arrow-right"></i> Lanjut</button>
            </div>
        </div>
    </div>

    {{-- Modal Hapus Siswa Lulus Step 2 --}}
    <div class="modal-overlay" id="modal-delete-graduated-step2">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Akhir Hapus (Tahap 2)</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                <strong>PERINGATAN KERAS:</strong> Menghapus data siswa yang sudah lulus akan menghapus data mereka secara **permanen dan tidak dapat dipulihkan kembali (irreversible)**. <br><br>
                Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan massal ini sekarang?
            </p>
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-graduated-step2')" style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitDeleteGraduatedForm()" style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i class="fas fa-trash-alt"></i> Ya, Hapus Semua</button>
            </div>
        </div>
    </div>

    @if(session('promote_success'))
    <div class="modal-overlay open" id="modal-promote-success">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Kenaikan Kelas Berhasil!</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                {{ session('promote_success') }}
            </p>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeModal('modal-promote-success')" style="margin: 0; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    @if(session('delete_graduated_success'))
    <div class="modal-overlay open" id="modal-delete-graduated-success">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Penghapusan Berhasil!</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                {{ session('delete_graduated_success') }}
            </p>
            <div class="modal-footer" style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="closeModal('modal-delete-graduated-success')" style="margin: 0; min-width: 120px;">Tutup</button>
            </div>
        </div>
    </div>
    @endif
    @endif
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }
        document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById('user-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-user');
                });
            }
        });

        window.submitUserForm = function () {
            const form = document.getElementById('user-form');
            if (form) {
                form.submit();
            }
        };

        function toggleRoleUI() {
            const role = document.getElementById('user-role').value;
            const siswaUI = document.getElementById('container-class-siswa');
            const siswaRow = document.getElementById('container-class-siswa-row');

            if (role === 'siswa') {
                siswaUI.style.display = 'block';
                if (siswaRow) siswaRow.style.display = 'flex';
            } else {
                siswaUI.style.display = 'none';
                if (siswaRow) siswaRow.style.display = 'none';
            }
        }

        function openImportModal() {
            openModal('modal-import');
        }

        function openAddModal() {
            document.getElementById('user-modal-title').textContent = 'Tambah User';
            document.getElementById('user-form').action = "{{ route('users.store') }}";
            document.getElementById('user-method').value = 'POST';
            document.getElementById('user-form').reset();
            document.getElementById('user-password').setAttribute('required', 'required');
            document.getElementById('user-role').value = "{{ request('role', 'siswa') }}";
            toggleRoleUI();
            openModal('modal-user');
        }

        function editUser(id, name, email, role, nis, classId, teacherClasses, noHp, gender) {
            document.getElementById('user-modal-title').textContent = 'Edit User';
            document.getElementById('user-form').action = '/users/' + id;
            document.getElementById('user-method').value = 'PUT';
            document.getElementById('user-name').value = name;
            document.getElementById('user-email').value = email;
            document.getElementById('user-role').value = role;
            document.getElementById('user-nis').value = nis;
            document.getElementById('user-nohp').value = noHp || '';
            document.getElementById('user-gender').value = gender || '';
            document.getElementById('user-class-siswa').value = classId;
            document.getElementById('user-password').value = '';
            document.getElementById('user-password').removeAttribute('required');

            toggleRoleUI();
            openModal('modal-user');
        }

        function openDeleteUserModal(id, name) {
            document.getElementById('form-delete-user').action = '/users/' + id;
            document.getElementById('delete-user-name').textContent = name;
            openModal('modal-delete-user');
        }

        function openDetailUserModal(user) {
            document.getElementById('detail-user-initial').textContent = user.name.charAt(0).toUpperCase();
            document.getElementById('detail-user-name').textContent = user.name;
            document.getElementById('detail-user-gender').textContent = user.gender;
            document.getElementById('detail-user-phone').textContent = user.phone;
            document.getElementById('detail-user-email').textContent = user.email;
            document.getElementById('detail-user-status').textContent = user.status;

            // Status Badge Styling
            const statusSpan = document.getElementById('detail-user-status');
            statusSpan.className = 'badge';
            if (user.status === 'Aktif') {
                statusSpan.classList.add('badge-success');
            } else if (user.status === 'Lulus') {
                statusSpan.classList.add('badge-info');
            } else {
                statusSpan.classList.add('badge-danger');
            }

            // Role Badge Styling and Text
            const roleBadge = document.getElementById('detail-user-role-badge');
            roleBadge.className = 'badge';
            if (user.role === 'admin') {
                roleBadge.textContent = 'Admin';
                roleBadge.classList.add('badge-danger');
            } else if (user.role === 'guru') {
                roleBadge.textContent = 'Guru BK';
                roleBadge.classList.add('badge-success');
            } else {
                roleBadge.textContent = 'Siswa';
                roleBadge.classList.add('badge-info');
            }

            // NIS / NIP Badge
            const nisNipBadge = document.getElementById('detail-user-nis-nip-badge');
            if (user.role === 'admin') {
                nisNipBadge.style.display = 'none';
            } else {
                nisNipBadge.style.display = 'inline';
                nisNipBadge.textContent = (user.role === 'guru' ? 'NIP: ' : 'NIS: ') + user.nis_nip;
            }

            // Class Row (For Siswa)
            const classRow = document.getElementById('detail-user-class-row');
            const guruBkRow = document.getElementById('detail-user-gurubk-row');
            if (user.role === 'siswa') {
                classRow.style.display = 'flex';
                document.getElementById('detail-user-class').textContent = user.class;
                if (guruBkRow) {
                    guruBkRow.style.display = 'flex';
                    document.getElementById('detail-user-gurubk').textContent = user.guru_bk;
                }
            } else {
                classRow.style.display = 'none';
                if (guruBkRow) guruBkRow.style.display = 'none';
            }

            // Classes Managed Row (For Guru BK)
            const classesManagedRow = document.getElementById('detail-user-classes-managed-row');
            if (user.role === 'guru') {
                classesManagedRow.style.display = 'flex';
                const listContainer = document.getElementById('detail-user-classes-managed-list');
                listContainer.innerHTML = '';
                if (user.classes_managed && user.classes_managed.length > 0) {
                    user.classes_managed.forEach(cls => {
                        const span = document.createElement('span');
                        span.className = 'badge badge-outline';
                        span.style.border = '1px solid var(--teal)';
                        span.style.color = 'var(--teal)';
                        span.style.background = '#f0fdfa';
                        span.innerHTML = '<i class="fas fa-chalkboard-teacher"></i> ' + cls;
                        listContainer.appendChild(span);
                    });
                } else {
                    listContainer.innerHTML = '<span class="text-muted" style="font-style: italic; font-size: 0.9rem;">Belum mengampu kelas apa pun</span>';
                }
            } else {
                classesManagedRow.style.display = 'none';
            }

            openModal('modal-detail-user');
        }

        window.toggleActionDropdown = function(event, button) {
            event.stopPropagation();
            const menu = button.nextElementSibling;
            
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) {
                    m.classList.remove('show');
                }
            });

            if (menu) {
                menu.classList.toggle('show');
            }
        };

        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        function openPromoteModalStep1() {
            openModal('modal-promote-step1');
        }

        function proceedToPromoteStep2() {
            closeModal('modal-promote-step1');
            openModal('modal-promote-step2');
        }

        function submitPromoteForm() {
            document.getElementById('form-promote-classes').submit();
        }

        function openDeleteGraduatedModalStep1() {
            openModal('modal-delete-graduated-step1');
        }

        function proceedToDeleteGraduatedStep2() {
            closeModal('modal-delete-graduated-step1');
            openModal('modal-delete-graduated-step2');
        }

        function submitDeleteGraduatedForm() {
            document.getElementById('form-delete-graduated').submit();
        }

        // Inisialisasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', toggleRoleUI);

        // Real-time search script
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            if (searchInput.value.trim().length > 0) {
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
                searchInput.focus();
            }

            let searchTimeout = null;
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.form.submit();
                }, 400);
            });
        }
    </script>
@endpush