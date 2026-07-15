@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Manajemen {{ ucfirst(request('role', 'Pengguna')) }}</h2>
            <p>Kelola data {{ request('role') }} sistem secara spesifik.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if(request('role') === 'siswa' || !request('role'))
                <button class="btn btn-secondary" onclick="openImportModal()"><i class="fas fa-file-excel"></i> Import
                    Siswa</button>
            @endif
            <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah User</button>
        </div>
    </div>

    <form class="filter-bar" method="GET">
        <input type="hidden" name="role" value="{{ request('role') }}">
        <input type="text" name="search" placeholder="Cari nama, identitas, email..." value="{{ request('search') }}">
    </form>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIS/NIP</th>
                        <th>Role</th>
                        <th>Akses Kelas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $i => $u)
                        <tr>
                            <td>{{ $users->firstItem() + $i }}</td>
                            <td><strong>{{ $u->name }}</strong><br><small class="text-muted">{{ $u->email }}</small></td>
                            <td>{{ $u->student?->nis ?? $u->teacher?->nip ?? '-' }}</td>
                            <td><span
                                    class="badge {{ $u->role === 'guru' ? 'badge-success' : ($u->role === 'siswa' ? 'badge-info' : 'badge-danger') }}">{{ $u->role_label }}</span>
                            </td>
                            <td>
                                @if($u->role === 'siswa')
                                    {{ $u->student?->class?->name ?? '—' }}
                                @elseif($u->role === 'guru')
                                    @if($u->teacher && $u->teacher->classes->count() > 0)
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            @foreach($u->teacher->classes as $cls)
                                                <span class="badge badge-outline"
                                                    style="border: 1px solid var(--teal); color: var(--teal); text-align: left;">
                                                    <i class="fas fa-chalkboard-teacher"></i> {{ $cls->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                @else
                                    <span class="text-muted">Akses Penuh</span>
                                @endif
                            </td>
                            <td><span
                                    class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="action-btns">
                                <button class="btn btn-secondary btn-sm"
                                    onclick="editUser({{ $u->id }},'{{ $u->name }}','{{ $u->email }}','{{ $u->role }}','{{ $u->student?->nis ?? $u->teacher?->nip ?? '' }}','{{ $u->student?->class_id ?? '' }}', {{ $u->teacher ? $u->teacher->classes->pluck('id') : '[]' }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('users.destroy', $u) }}" style="display:inline"
                                    onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted" style="text-align:center">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
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

                <div class="form-row">
                    <div class="field-group"><label>Nama Lengkap</label><input type="text" name="name" id="user-name"
                            required></div>
                    <div class="field-group"><label>NIS / NIP</label><input type="text" name="nis_nip" id="user-nis"></div>
                </div>

                <div class="form-row">
                    <div class="field-group">
                        <label>Role</label>
                        <select name="role" id="user-role" onchange="toggleRoleUI()">
                            <option value="siswa" {{ request('role') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru BK</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            @if(auth()->user()->isSuperAdmin())
                            <option value="superadmin">Super Admin</option>@endif
                        </select>
                    </div>
                    <div class="field-group" id="container-class-siswa">
                        <label>Kelas Siswa</label>
                        <select name="class_id" id="user-class-siswa">
                            <option value="">— Pilih Kelas —</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- UI Khusus Guru: Tabel Pemilihan Kelas --}}
                <style>
                    .class-selection-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                        gap: 12px;
                        max-height: 300px;
                        overflow-y: auto;
                        padding: 5px;
                    }

                    .class-card {
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        padding: 12px;
                        cursor: pointer;
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                        transition: all 0.2s;
                        position: relative;
                        background: #fff;
                    }

                    .class-card:hover {
                        border-color: #cbd5e1;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                    }

                    .class-card:has(input:checked) {
                        border-color: var(--teal, #0d9488);
                        background-color: #f0fdfa;
                        box-shadow: 0 0 0 1px var(--teal, #0d9488);
                    }

                    .class-card-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }

                    .class-card .class-name {
                        font-weight: 600;
                        font-size: 0.95rem;
                        color: #1e293b;
                    }

                    .class-card input[type="checkbox"] {
                        width: 18px;
                        height: 18px;
                        cursor: pointer;
                        accent-color: var(--teal, #0d9488);
                    }

                    .class-teacher-badge {
                        font-size: 0.75rem;
                        padding: 4px 8px;
                        border-radius: 4px;
                        display: inline-flex;
                        align-items: center;
                        gap: 4px;
                        width: fit-content;
                    }

                    .class-teacher-badge.warning {
                        color: #b45309;
                        background: #fef3c7;
                    }

                    .class-teacher-badge.empty {
                        color: #64748b;
                        font-style: italic;
                    }
                </style>
                <div id="container-class-guru" style="display: none; margin-top: 15px;">
                    <label style="display: block; margin-bottom: 10px; font-weight: 600;">Pilih Kelas Kelolaan (Guru
                        BK)</label>
                    <div class="class-selection-grid">
                        @foreach($classes as $c)
                            <label class="class-card">
                                <div class="class-card-header">
                                    <span class="class-name">{{ $c->name }}</span>
                                    <input type="checkbox" name="teacher_class_ids[]" value="{{ $c->id }}"
                                        class="class-checkbox" id="check-{{ $c->id }}">
                                </div>
                                @if($c->teacher)
                                    <div class="class-teacher-badge warning">
                                        <i class="fas fa-user-tie"></i> {{ $c->teacher->user->name }}
                                    </div>
                                @else
                                    <div class="class-teacher-badge empty">
                                        <i class="fas fa-info-circle"></i> Belum ada guru
                                    </div>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    <p
                        style="font-size: 0.8rem; color: #64748b; margin-top: 12px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-info-circle text-blue-500"></i> Jika kelas sudah memiliki guru, memilihnya akan
                        memindahkan hak akses kelas ke guru ini (ambil alih).
                    </p>
                </div>

                <div class="form-row">
                    <div class="field-group"><label>Email</label><input type="email" name="email" id="user-email" required>
                    </div>
                    <div class="field-group"><label>Password</label><input type="password" name="password"
                            id="user-password"></div>
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
                        <li><strong>nama</strong> : Nama lengkap siswa</li>
                        <li><strong>email</strong> : Alamat email (unik)</li>
                        <li><strong>password</strong> : (Opsional) Jika kosong: password123</li>
                        <li><strong>nis</strong> : (Opsional) Nomor Induk Siswa</li>
                        <li><strong>kelas</strong> : (Opsional) Nama kelas (harus persis)</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-import')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }

        function toggleRoleUI() {
            const role = document.getElementById('user-role').value;
            const siswaUI = document.getElementById('container-class-siswa');
            const guruUI = document.getElementById('container-class-guru');

            siswaUI.style.display = (role === 'siswa') ? 'block' : 'none';
            guruUI.style.display = (role === 'guru') ? 'block' : 'none';
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
            toggleRoleUI();
            openModal('modal-user');
        }

        function editUser(id, name, email, role, nis, classId, teacherClasses) {
            document.getElementById('user-modal-title').textContent = 'Edit User';
            document.getElementById('user-form').action = '/users/' + id;
            document.getElementById('user-method').value = 'PUT';
            document.getElementById('user-name').value = name;
            document.getElementById('user-email').value = email;
            document.getElementById('user-role').value = role;
            document.getElementById('user-nis').value = nis;
            document.getElementById('user-class-siswa').value = classId;
            document.getElementById('user-password').removeAttribute('required');

            // Reset Checkboxes
            document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);

            // Set Checkboxes untuk Guru
            if (teacherClasses && Array.isArray(teacherClasses)) {
                teacherClasses.forEach(classId => {
                    const cb = document.getElementById('check-' + classId);
                    if (cb) cb.checked = true;
                });
            }

            toggleRoleUI();
            openModal('modal-user');
        }

        // Inisialisasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', toggleRoleUI);
    </script>
@endpush