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

        .premium-spinner {
            width: 48px;
            height: 48px;
            border: 5px solid #e2e8f0;
            border-top-color: var(--teal, #0f766e);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endpush

@section('content')
    @php
        $currentRole = request('role', 'siswa');
        $roleLabels = [
            'admin' => 'Admin',
            'guru' => 'Guru BK',
            'siswa' => 'Siswa',
        ];
        $activeLabel = $roleLabels[$currentRole] ?? 'User';
    @endphp
    <div class="page-header-row">
        <div class="page-header">
            <h2>Manajemen {{ $activeLabel }}</h2>
            <p>Kelola data {{ strtolower($activeLabel) }} sistem secara spesifik.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            @if(request('role') === 'siswa')
                <button class="btn btn-secondary" onclick="openImportModal()"
                    style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; font-weight: 500;"><i
                        class="fas fa-file-excel" style="color: #10b981; margin-right: 4px;"></i> Import Siswa</button>
            @elseif(request('role') === 'guru')
                <button class="btn btn-secondary" onclick="openImportGuruModal()"
                    style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; font-weight: 500; margin-right: 8px;"><i
                        class="fas fa-file-excel" style="color: #10b981; margin-right: 4px;"></i> Import Guru BK</button>
            @endif
            <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah
                {{ $activeLabel }}</button>
            @if(request('role') === 'siswa')
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <button class="btn btn-secondary" onclick="toggleActionDropdown(event, this)"
                        style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; padding: 9px 14px; margin: 0; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" style="right: 0; left: auto; min-width: 180px;">
                        <button type="button" onclick="openPromoteModalStep1()"
                            style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 13px; color: var(--charcoal); cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-arrow-up" style="color: var(--teal); width: 16px;"></i> Naikkan Kelas
                        </button>
                        <button type="button" onclick="openDeleteGraduatedModalStep1()"
                            style="width: 100%; text-align: left; background: none; border: none; padding: 10px 16px; font-size: 13px; color: var(--danger); cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-trash-alt" style="color: var(--danger); width: 16px;"></i> Hapus Siswa Lulus
                        </button>
                    </div>
                </div>
                <form id="form-promote-classes" method="POST" action="{{ route('users.promote-classes') }}"
                    style="display: none;">
                    @csrf
                </form>
                <form id="form-delete-graduated" method="POST" action="{{ route('users.delete-graduated') }}"
                    style="display: none;">
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
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user?->name ?? '-' }}
                    </option>
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
                            <th>Nama</th>
                            <th>NIS</th>
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
                                        <td><strong>{{ $u->name }}</strong></td>
                                        <td>{{ $u->student?->nis ?? '-' }}</td>
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
                                                <button class="btn btn-primary btn-sm"
                                                    style="display: inline-flex; align-items: center; gap: 4px;" title="Detail Siswa"
                                                    onclick="openDetailUserModal({{ json_encode([
                                'role' => 'siswa',
                                'name' => $u->name,
                                'nis_nip' => $u->student?->nis ?? '-',
                                'class' => $u->student?->class?->name ?? '-',
                                'gender' => $u->jenis_kelamin === 'L' ? 'Laki-laki' : ($u->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                                'phone' => $u->no_hp ?? '-',
                                'email' => $u->email,
                                'status' => $u->is_active ? 'Aktif' : 'Lulus',
                                'guru_bk' => $u->student?->class?->teacher?->user?->name ?? '-',
                                'spesialisasi' => '-',
                                'classes_managed' => []
                            ]) }})"><i class="fas fa-eye"></i> Detail</button>

                                                <div class="dropdown">
                                                    <button class="btn btn-secondary btn-sm" onclick="toggleActionDropdown(event, this)">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <button type="button"
                                                            onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}','{{ $u->student?->nis ?? $u->teacher?->nip ?? '' }}','{{ $u->student?->class_id ?? '' }}', [],'{{ $u->no_hp }}','{{ $u->jenis_kelamin }}', '')">
                                                            <i class="fas fa-edit" style="color: #f59e0b;"></i> Edit
                                                        </button>
                                                        <button type="button" class="delete-btn"
                                                            onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
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
                                        <td><strong>{{ $u->name }}</strong><br><small class="text-muted">{{ $u->email ?? '-' }}</small></td>
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
                                                <button class="btn btn-primary btn-sm"
                                                    style="display: inline-flex; align-items: center; gap: 4px;" title="Detail User"
                                                    onclick="openDetailUserModal({{ json_encode([
                                'role' => $u->role,
                                'name' => $u->name,
                                'nis_nip' => $u->teacher?->nip ?? '-',
                                'class' => '-',
                                'gender' => $u->jenis_kelamin === 'L' ? 'Laki-laki' : ($u->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                                'phone' => $u->no_hp ?? '-',
                                'email' => $u->email,
                                'status' => $u->is_active ? 'Aktif' : 'Nonaktif',
                                'spesialisasi' => $u->teacher?->spesialisasi ?? '-',
                                'classes_managed' => $u->teacher ? $u->teacher->classes->pluck('name')->toArray() : []
                            ]) }})"><i class="fas fa-eye"></i> Detail</button>

                                                <div class="dropdown">
                                                    <button class="btn btn-secondary btn-sm" onclick="toggleActionDropdown(event, this)">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <button type="button"
                                                            onclick="editUser({{ $u->id }},'{{ addslashes($u->name) }}','{{ $u->email }}','{{ $u->role }}','{{ $u->student?->nis ?? $u->teacher?->nip ?? '' }}','{{ $u->student?->class_id ?? '' }}', [],'{{ $u->no_hp }}','{{ $u->jenis_kelamin }}', '{{ addslashes($u->teacher?->spesialisasi ?? '') }}')">
                                                            <i class="fas fa-edit" style="color: #f59e0b;"></i> Edit
                                                        </button>
                                                        <button type="button" class="delete-btn"
                                                            onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}')">
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
    <div class="modal-overlay @if($errors->any() && !session('success_modal')) open @endif" id="modal-user">
        <div class="modal" style="max-width: 800px; width: 95%;">
            <div class="modal-header">
                <h3 id="user-modal-title">{{ old('_method') === 'PUT' ? 'Edit ' . $activeLabel : 'Tambah ' . $activeLabel }}
                </h3>
                <button class="modal-close" onclick="closeModal('modal-user')">✕</button>
            </div>
            <form method="POST" id="user-form" action="{{ old('form_action', route('users.store')) }}">
                @csrf
                <input type="hidden" name="_method" id="user-method" value="{{ old('_method', 'POST') }}">
                <input type="hidden" name="form_action" id="user-form-action"
                    value="{{ old('form_action', route('users.store')) }}">
                <input type="hidden" name="role" id="user-role" value="{{ old('role', request('role', 'siswa')) }}">

                <div class="field-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="user-name" required value="{{ old('name') }}">
                    @error('name')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                </div>

                <div class="field-group">
                    <label
                        id="user-nis-label">{{ old('role', request('role', 'siswa')) === 'siswa' ? 'NIS' : 'NIP' }}</label>
                    <input type="text" name="nis_nip" id="user-nis" value="{{ old('nis_nip') }}">
                    @error('nis_nip')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                </div>

                <div class="field-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="user-gender">
                        <option value="">— Pilih Jenis Kelamin —</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                    </select>
                    @error('jenis_kelamin')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                </div>

                <div id="container-class-siswa-row"
                    style="margin-bottom: 15px; display: {{ old('role', request('role', 'siswa')) === 'siswa' ? 'flex' : 'none' }};">
                    <div class="field-group" id="container-class-siswa"
                        style="width: 100%; margin-bottom: 0; display: {{ old('role', request('role', 'siswa')) === 'siswa' ? 'block' : 'none' }};">
                        <label>Kelas Siswa</label>
                        <select name="class_id" id="user-class-siswa">
                            <option value="">— Pilih Kelas —</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')<span
                        style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div id="container-spesialisasi-row"
                    style="margin-bottom: 15px; display: {{ old('role', request('role', 'siswa')) === 'guru' ? 'flex' : 'none' }};">
                    <div class="field-group" id="container-spesialisasi" style="width: 100%; margin-bottom: 0;">
                        <label>Spesialisasi</label>
                        <input type="text" name="spesialisasi" id="user-spesialisasi" placeholder="Contoh: Spesialis Karir & Hubungan Sosial" value="{{ old('spesialisasi') }}">
                        @error('spesialisasi')<span
                        style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="field-group">
                    <label>No. HP <span style="color:var(--muted);font-weight:normal;">(Opsional)</span></label>
                    <input type="text" name="no_hp" id="user-nohp" placeholder="Contoh: 08123456789"
                        value="{{ old('no_hp') }}">
                    @error('no_hp')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                </div>

                <div class="field-group">
                    <label id="user-email-label">Email <span
                            style="color:var(--muted);font-weight:normal;">(Opsional)</span></label>
                    <input type="email" name="email" id="user-email" value="{{ old('email') }}">
                    @error('email')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
                </div>

                <div class="field-group">
                    <label
                        id="user-password-label">{{ old('_method') === 'PUT' ? 'Password (Kosongkan jika tidak diubah)' : 'Password' }}</label>
                    <input type="password" name="password" id="user-password" minlength="6"
                        placeholder="Minimal 6 karakter">
                    @error('password')<span
                    style="color:var(--danger);font-size:12px;margin-top:4px;display:block;">{{ $message }}</span>@enderror
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
            <form id="form-import-siswa" method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="force_update" id="import-force-update" value="0">
                <div class="form-row">
                    <div class="field-group">
                        <label>File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            style="padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; background: #f8f9fa;">
                    </div>

                </div>
                <a href="/E-BK_Template_Import_Siswa.xlsx" download="E-BK_Template_Import_Siswa.xlsx" class="btn btn-primary"><i class="fas fa-download"></i>Unduh Template</a>
                <div
                    style="margin-top: 15px; font-size: 0.9rem; color: #475569; background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                    <p style="margin-bottom: 8px; font-weight: 600; color: #1e3a8a;"><i class="fas fa-info-circle"></i>
                        Panduan Proses Import Data:</p>
                    <ol style="margin-left: 20px; line-height: 1.5;">
                        <li>Pastikan seluruh <strong>Data Kelas</strong> sudah diatur/ditambahkan di menu Data Kelas sebelum
                            melakukan import.</li>
                        <li>Unduh template file Excel melalui tombol <strong>"Unduh Template"</strong> di atas.</li>
                        <li>Buka file template tersebut dan isi data siswa baru sesuai kolom yang disediakan.</li>
                        <li>Pastikan nama kelas di kolom <strong>kelas</strong> sesuai dengan nama kelas yang terdaftar di
                            sistem.</li>
                        <li>Pastikan kolom <strong>nis</strong>, <strong>nama</strong>, <strong>jenis_kelamin</strong>
                            (L/P), dan <strong>email</strong> terisi dengan benar.</li>
                        <li>Simpan file Excel tersebut setelah selesai diisi.</li>
                        <li>Pilih file Excel yang telah disimpan menggunakan kolom input file di atas.</li>
                        <li>Klik tombol <strong>"Import"</strong> di bawah untuk memulai proses unggah data siswa.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-import')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-import-guru">
        <div class="modal" style="max-width: 500px; width: 95%;">
            <div class="modal-header">
                <h3>Import Data Guru BK</h3>
                <button class="modal-close" onclick="closeModal('modal-import-guru')">✕</button>
            </div>
            <form id="form-import-guru" method="POST" action="{{ route('users.import-guru') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="force_update" id="import-guru-force-update" value="0">
                <div class="form-row">
                    <div class="field-group">
                        <label>File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            style="padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; background: #f8f9fa;">
                    </div>
                </div>
                <a href="/E-BK_Template_Import_Guru_BK.xlsx" download="E-BK_Template_Import_Guru_BK.xlsx" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-download"></i>Unduh Template</a>
                <div
                    style="margin-top: 15px; font-size: 0.9rem; color: #475569; background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                    <p style="margin-bottom: 8px; font-weight: 600; color: #1e3a8a;"><i class="fas fa-info-circle"></i>
                        Panduan Proses Import Data Guru BK:</p>
                    <ol style="margin-left: 20px; line-height: 1.5;">
                        <li>Unduh template file Excel melalui tombol <strong>"Unduh Template"</strong> di atas.</li>
                        <li>Buka file template tersebut dan isi data Guru BK baru sesuai kolom yang disediakan.</li>
                        <li>Pastikan nama, nip, jenis_kelamin (L/P), spesialisasi, no_hp (opsional), email (wajib), dan password terisi dengan benar.</li>
                        <li>Simpan file Excel tersebut setelah selesai diisi.</li>
                        <li>Pilih file Excel yang telah disimpan menggunakan kolom input file di atas.</li>
                        <li>Klik tombol <strong>"Import"</strong> di bawah untuk memulai proses unggah data Guru BK.</li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-import-guru')">Batal</button>
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
                <div
                    style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 20px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #f0fdfa; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--teal); font-weight: bold;"
                        id="detail-user-initial">
                        U
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;"
                                id="detail-user-name">Nama User</h4>
                            <span class="badge" id="detail-user-role-badge"
                                style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px;">Siswa</span>
                        </div>
                        <span style="font-size: 0.85rem; color: #64748b;" id="detail-user-nis-nip-badge">NIS: -</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-class-row">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Kelas</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-class">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-gurubk-row">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Guru
                            BK</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-gurubk">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-classes-managed-row">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Kelas
                            Diampu</span>
                        <div id="detail-user-classes-managed-list"
                            style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;" id="detail-user-spesialisasi-row">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Spesialisasi</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-spesialisasi">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Jenis
                            Kelamin</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;"
                            id="detail-user-gender">Laki-laki</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">No.
                            WhatsApp / HP</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;" id="detail-user-phone">-</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Email</span>
                        <span style="font-size: 0.95rem; color: #334155; font-weight: 500;"
                            id="detail-user-email">email@example.com</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span
                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 600;">Status
                            Akun</span>
                        <span style="width: fit-content;" id="detail-user-status">Aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-user')"
                    style="margin: 0; width: 100%; display: flex; justify-content: center; align-items: center; text-align: center;">Tutup</button>
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
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Kenaikan Kelas
                    (Tahap 1)</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    Apakah Anda yakin ingin menaikkan seluruh siswa ke tingkat kelas berikutnya? <br><br>
                    Siswa kelas <strong>X</strong> akan naik ke kelas <strong>XI</strong>, kelas <strong>XI</strong> naik ke
                    kelas <strong>XII</strong>, dan kelas <strong>XII</strong> akan dinyatakan <strong>Lulus</strong>.
                </p>
                <div class="modal-footer"
                    style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-promote-step1')"
                        style="margin: 0;">Batal</button>
                    <button type="button" class="btn btn-gold" onclick="proceedToPromoteStep2()" style="margin: 0;"><i
                            class="fas fa-arrow-right"></i> Lanjut</button>
                </div>
            </div>
        </div>

        {{-- Modal Naikkan Kelas Step 2 --}}
        <div class="modal-overlay" id="modal-promote-step2">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Akhir (Tahap 2)
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    <strong>PERINGATAN KESELAMATAN:</strong> Tindakan ini akan mengubah database secara massal dan <strong>tidak
                        dapat dibatalkan (undo)</strong>. <br><br>
                    Pastikan Anda sudah mencadangkan database atau memeriksa data siswa sebelum melanjutkan. Apakah Anda
                    benar-benar yakin ingin memproses kenaikan kelas sekarang?
                </p>
                <div class="modal-footer"
                    style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-promote-step2')"
                        style="margin: 0;">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="submitPromoteForm()"
                        style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i
                            class="fas fa-check"></i> Ya, Naikkan Kelas</button>
                </div>
            </div>
        </div>
    @endif


    @if(session('import_error') || request('import_error'))
        <div class="modal-overlay open" id="modal-import-error">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Import Gagal</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    {{ session('import_error') ?? request('import_error') }}
                </p>
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-danger" onclick="closeModal('modal-import-error')"
                        style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff; min-width: 120px;">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    @if(session('success_modal') || request('import_success'))
        <div class="modal-overlay open" id="modal-success-notification">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--success, #10b981); margin-bottom: 15px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Berhasil</h3>
                <p style="color: #64748b; font-size: 0.95rem; margin-top: 10px; line-height: 1.5;">
                    {{ session('success_modal') ?? (request('role') === 'guru' ? 'Seluruh data Guru BK berhasil diimport dengan sukses ke dalam database.' : 'Seluruh data siswa berhasil diimport dengan sukses ke dalam database.') }}
                </p>
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="closeModal('modal-success-notification')"
                        style="margin: 0; min-width: 120px;">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Hapus Siswa Lulus Step 1 --}}
    @if(request('role') === 'siswa')
        <div class="modal-overlay" id="modal-delete-graduated-step1">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Hapus Siswa Lulus (Tahap 1)
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus **seluruh** siswa yang berstatus **Lulus** dari database? <br><br>
                    Tindakan ini akan menghapus akun login dan riwayat siswa terkait.
                </p>
                <div class="modal-footer"
                    style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-graduated-step1')"
                        style="margin: 0;">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="proceedToDeleteGraduatedStep2()"
                        style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i
                            class="fas fa-arrow-right"></i> Lanjut</button>
                </div>
            </div>
        </div>

        {{-- Modal Hapus Siswa Lulus Step 2 --}}
        <div class="modal-overlay" id="modal-delete-graduated-step2">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Konfirmasi Akhir Hapus
                    (Tahap 2)</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    <strong>PERINGATAN KERAS:</strong> Menghapus data siswa yang sudah lulus akan menghapus data mereka secara
                    **permanen dan tidak dapat dipulihkan kembali (irreversible)**. <br><br>
                    Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan massal ini sekarang?
                </p>
                <div class="modal-footer"
                    style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-graduated-step2')"
                        style="margin: 0;">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="submitDeleteGraduatedForm()"
                        style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff;"><i
                            class="fas fa-trash-alt"></i> Ya, Hapus Semua</button>
                </div>
            </div>
        </div>
    @endif

    @if(session('promote_success'))
        <div class="modal-overlay open" id="modal-promote-success">
            <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Kenaikan Kelas Berhasil!
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    {{ session('promote_success') }}
                </p>
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="closeModal('modal-promote-success')"
                        style="margin: 0; min-width: 120px;">Tutup</button>
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
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="closeModal('modal-delete-graduated-success')"
                        style="margin: 0; min-width: 120px;">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Duplikasi NIS saat Import --}}
    <div class="modal-overlay" id="modal-confirm-import-duplicate" style="z-index: 1100;">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Duplikasi NIS Terdeteksi
            </h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Terdapat <strong id="import-duplicate-count">0</strong> siswa dengan NIS yang sama sudah terdaftar di
                database.
                <br><br>
                Apakah Anda ingin melanjutkan dan <strong>memperbarui</strong> data siswa tersebut dengan data terbaru dari
                Excel, atau membatalkan proses import?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-import-duplicate')"
                    style="margin: 0; min-width: 120px;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="proceedImportWithUpdate()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal); min-width: 120px;">Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Duplikasi Guru BK saat Import --}}
    <div class="modal-overlay" id="modal-confirm-import-guru-duplicate" style="z-index: 1100;">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Duplikasi Guru BK Terdeteksi</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Terdapat <strong id="import-guru-duplicate-count">0</strong> data Guru BK yang sudah terdaftar di database (berdasarkan NIP atau Email).
                <br><br>
                Apakah Anda ingin melanjutkan dan <strong>memperbarui</strong> data Guru BK tersebut dengan data terbaru dari Excel, atau membatalkan proses import?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-import-guru-duplicate')"
                    style="margin: 0; min-width: 120px;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="proceedImportGuruWithUpdate()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal); min-width: 120px;">Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- Loading Overlay for Import --}}
    <div class="modal-overlay" id="modal-import-loading" style="z-index: 1200; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 32px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="margin-bottom: 20px; display: flex; justify-content: center;">
                <div class="premium-spinner"></div>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Mengimport Data</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Sedang memproses dan menyimpan data siswa ke dalam database. Mohon jangan menutup halaman ini...
            </p>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }
        document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });

        document.addEventListener("DOMContentLoaded", function () {
            // Clean up import query parameters from address bar to prevent modal popup on refresh
            if (window.location.search.includes('import_success=1') || window.location.search.includes('import_error=')) {
                const url = new URL(window.location);
                url.searchParams.delete('import_success');
                url.searchParams.delete('import_error');
                window.history.replaceState({}, document.title, url.toString());
            }

            const form = document.getElementById('user-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-user');
                });
            }

            const importForm = document.getElementById('form-import-siswa');
            if (importForm) {
                importForm.addEventListener('submit', function (event) {
                    const forceUpdateInput = document.getElementById('import-force-update');
                    if (forceUpdateInput.value === '1') {
                        return;
                    }

                    event.preventDefault();

                    const formData = new FormData(importForm);
                    const submitBtn = importForm.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memeriksa...';

                    fetch("{{ route('users.import-check') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Gagal memeriksa data');
                            }
                            return response.json();
                        })
                        .then(data => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;

                            if (data.has_duplicates) {
                                document.getElementById('import-duplicate-count').textContent = data.duplicates_count;
                                openModal('modal-confirm-import-duplicate');
                            } else {
                                forceUpdateInput.value = '0';
                                closeModal('modal-import');
                                openModal('modal-import-loading');
                                importForm.submit();
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                            closeModal('modal-import');
                            openModal('modal-import-loading');
                            importForm.submit();
                        });
                });
            }

            const importGuruForm = document.getElementById('form-import-guru');
            if (importGuruForm) {
                importGuruForm.addEventListener('submit', function (event) {
                    const forceUpdateInput = document.getElementById('import-guru-force-update');
                    if (forceUpdateInput.value === '1') {
                        return;
                    }

                    event.preventDefault();

                    const formData = new FormData(importGuruForm);
                    const submitBtn = importGuruForm.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memeriksa...';

                    fetch("{{ route('users.import-guru-check') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Gagal memeriksa data');
                            }
                            return response.json();
                        })
                        .then(data => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;

                            if (data.has_duplicates) {
                                document.getElementById('import-guru-duplicate-count').textContent = data.duplicates_count;
                                openModal('modal-confirm-import-guru-duplicate');
                            } else {
                                forceUpdateInput.value = '0';
                                closeModal('modal-import-guru');
                                openModal('modal-import-loading');
                                importGuruForm.submit();
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                            closeModal('modal-import-guru');
                            openModal('modal-import-loading');
                            importGuruForm.submit();
                        });
                });
            }
        });

        window.submitUserForm = function () {
            const form = document.getElementById('user-form');
            if (form) {
                form.submit();
            }
        };

        window.proceedImportWithUpdate = function () {
            document.getElementById('import-force-update').value = '1';
            closeModal('modal-confirm-import-duplicate');
            closeModal('modal-import');
            openModal('modal-import-loading');
            document.getElementById('form-import-siswa').submit();
        };

        window.proceedImportGuruWithUpdate = function () {
            document.getElementById('import-guru-force-update').value = '1';
            closeModal('modal-confirm-import-guru-duplicate');
            closeModal('modal-import-guru');
            openModal('modal-import-loading');
            document.getElementById('form-import-guru').submit();
        };

        window.openImportGuruModal = function () {
            openModal('modal-import-guru');
        };

        function toggleRoleUI() {
            const role = document.getElementById('user-role').value;
            const siswaUI = document.getElementById('container-class-siswa');
            const siswaRow = document.getElementById('container-class-siswa-row');
            const spesialisasiRow = document.getElementById('container-spesialisasi-row');
            const emailInput = document.getElementById('user-email');
            const emailLabel = document.getElementById('user-email-label');

            if (role === 'siswa') {
                siswaUI.style.display = 'block';
                if (siswaRow) siswaRow.style.display = 'flex';
                if (spesialisasiRow) spesialisasiRow.style.display = 'none';
                if (emailInput) emailInput.removeAttribute('required');
                if (emailLabel) emailLabel.innerHTML = 'Email <span style="color:var(--muted);font-weight:normal;">(Opsional)</span>';
            } else if (role === 'guru') {
                siswaUI.style.display = 'none';
                if (siswaRow) siswaRow.style.display = 'none';
                if (spesialisasiRow) spesialisasiRow.style.display = 'flex';
                if (emailInput) emailInput.setAttribute('required', 'required');
                if (emailLabel) emailLabel.innerHTML = 'Email';
            } else {
                siswaUI.style.display = 'none';
                if (siswaRow) siswaRow.style.display = 'none';
                if (spesialisasiRow) spesialisasiRow.style.display = 'none';
                if (emailInput) emailInput.setAttribute('required', 'required');
                if (emailLabel) emailLabel.innerHTML = 'Email';
            }
        }

        function openImportModal() {
            openModal('modal-import');
        }

        function openImportGuruModal() {
            openModal('modal-import-guru');
        }

        function openAddModal() {
            let role = "{{ request('role', 'siswa') }}";
            let roleLabel = role === 'admin' ? 'Admin' : (role === 'guru' ? 'Guru BK' : 'Siswa');
            document.getElementById('user-modal-title').textContent = 'Tambah ' + roleLabel;
            document.getElementById('user-nis-label').textContent = role === 'siswa' ? 'NIS' : 'NIP';
            document.getElementById('user-password-label').textContent = 'Password';
            document.getElementById('user-form').action = "{{ route('users.store') }}";
            document.getElementById('user-form-action').value = "{{ route('users.store') }}";
            document.getElementById('user-method').value = 'POST';
            document.getElementById('user-form').reset();
            document.getElementById('user-password').setAttribute('required', 'required');
            document.getElementById('user-role').value = role;
            toggleRoleUI();
            openModal('modal-user');
        }

        function editUser(id, name, email, role, nis, classId, teacherClasses, noHp, gender, spesialisasi) {
            let roleLabel = role === 'admin' ? 'Admin' : (role === 'guru' ? 'Guru BK' : 'Siswa');
            document.getElementById('user-modal-title').textContent = 'Edit ' + roleLabel;
            document.getElementById('user-nis-label').textContent = role === 'siswa' ? 'NIS' : 'NIP';
            document.getElementById('user-password-label').textContent = 'Password (Kosongkan jika tidak diubah)';
            document.getElementById('user-form').action = '/users/' + id;
            document.getElementById('user-form-action').value = '/users/' + id;
            document.getElementById('user-method').value = 'PUT';
            document.getElementById('user-name').value = name;
            document.getElementById('user-email').value = email || '';
            document.getElementById('user-role').value = role;
            document.getElementById('user-nis').value = nis;
            document.getElementById('user-nohp').value = noHp || '';
            document.getElementById('user-gender').value = gender || '';
            document.getElementById('user-class-siswa').value = classId;
            document.getElementById('user-spesialisasi').value = spesialisasi || '';
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
            document.getElementById('detail-user-email').textContent = user.email || '-';
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

            // Spesialisasi Row (For Guru BK)
            const spesialisasiRow = document.getElementById('detail-user-spesialisasi-row');
            if (user.role === 'guru') {
                if (spesialisasiRow) {
                    spesialisasiRow.style.display = 'flex';
                    document.getElementById('detail-user-spesialisasi').textContent = user.spesialisasi || '-';
                }
            } else {
                if (spesialisasiRow) spesialisasiRow.style.display = 'none';
            }

            openModal('modal-detail-user');
        }

        window.toggleActionDropdown = function (event, button) {
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

        document.addEventListener('click', function () {
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