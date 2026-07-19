@extends('layouts.app')
@section('title', 'Manajemen Kelas')
@section('page-title', 'Manajemen Kelas')

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Manajemen Kelas</h2>
            <p>Kelola daftar kelas dan pembagian guru BK pengampu.</p>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah Kelas</button>
    </div>

    <form class="filter-bar" method="GET">
        <input type="text" name="search" id="search-input" placeholder="Cari nama kelas atau wali kelas..."
            value="{{ request('search') }}">

        <select name="teacher_id" onchange="this.form.submit()" style="width: auto; min-width: 180px;">
            <option value="">Semua Guru BK</option>
            @foreach ($teachers as $t)
                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user->name }}</option>
            @endforeach
        </select>

        <select name="tingkat" onchange="this.form.submit()" style="width: auto; min-width: 150px;">
            <option value="">Semua Tingkat</option>
            <option value="X" {{ request('tingkat') === 'X' ? 'selected' : '' }}>Kelas X</option>
            <option value="XI" {{ request('tingkat') === 'XI' ? 'selected' : '' }}>Kelas XI</option>
            <option value="XII" {{ request('tingkat') === 'XII' ? 'selected' : '' }}>Kelas XII</option>
        </select>

        <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
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
                    <tr>
                        <th style="width: 60px; text-align: center;">No</th>
                        <th>Nama Kelas</th>
                        <th>Wali Kelas</th>
                        <th>Guru BK Pengampu</th>
                        <th style="text-align: center;">Jumlah Siswa</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $i => $c)
                        <tr>
                            <td style="text-align: center;">{{ $classes->firstItem() + $i }}</td>
                            <td><strong>{{ $c->name }}</strong></td>
                            <td>{{ $c->wali_kelas ?? '-' }}</td>
                            <td>
                                @if ($c->teacher)
                                    <span class="badge badge-success">
                                        <i class="fas fa-user-tie"></i> {{ $c->teacher->user->name }}
                                    </span>
                                @else
                                    <span class="text-muted"><i>Belum ditugaskan</i></span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-outline" style="border: 1px solid var(--teal); color: var(--teal);">
                                    {{ $c->students->count() }} Siswa
                                </span>
                            </td>
                            <td class="action-btns" style="text-align: center;">
                                <button class="btn btn-secondary btn-sm"
                                    onclick="editClass({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ addslashes($c->wali_kelas ?? '') }}', '{{ $c->teacher_id ?? '' }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm"
                                    onclick="openDeleteModal({{ $c->id }}, '{{ addslashes($c->name) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted" style="text-align:center">Data kelas tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $classes->links('vendor.pagination.custom') }}
    </div>
@endsection

@push('modals')
    {{-- Modal Tambah/Edit Kelas --}}
    <div class="modal-overlay" id="modal-class">
        <div class="modal" style="max-width: 500px; width: 95%;">
            <div class="modal-header">
                <h3 id="class-modal-title">Tambah Kelas</h3>
                <button class="modal-close" onclick="closeModal('modal-class')">✕</button>
            </div>
            <form method="POST" id="class-form" action="{{ route('kelas.store') }}">
                @csrf
                <input type="hidden" name="_method" id="class-method" value="POST">

                <div class="field-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="name" id="class-name" required placeholder="Contoh: XII RPL 1">
                </div>

                <div class="field-group">
                    <label>Wali Kelas</label>
                    <input type="text" name="wali_kelas" id="class-wali" placeholder="Nama lengkap wali kelas (opsional)">
                </div>

                <div class="field-group">
                    <label>Guru BK Pengampu</label>
                    <select name="teacher_id" id="class-teacher">
                        <option value="">— Pilih Guru BK —</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-class')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div class="modal-overlay" id="modal-delete-class">
        <div class="modal" style="max-width: 400px; text-align: center;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Hapus Kelas?</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menghapus kelas <strong id="delete-class-name"></strong>? Data relasi siswa di kelas
                ini akan terpengaruh.
            </p>
            <form method="POST" id="form-delete-class" style="margin-top: 20px;">
                @csrf
                @method('DELETE')
                <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 0;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-class')"
                        style="margin: 0;">Batal</button>
                    <button type="submit" class="btn btn-danger" style="margin: 0;"><i class="fas fa-trash"></i> Ya,
                        Hapus</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Simpan --}}
    <div class="modal-overlay" id="modal-confirm-class">
        <div class="modal" style="max-width: 400px; text-align: center;">
            <div style="font-size: 3rem; color: var(--teal); margin-bottom: 15px;">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3>Simpan Data Kelas?</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menyimpan perubahan data kelas ini?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-class')"
                    style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitClassForm()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya,
                    Simpan</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById('class-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-class');
                });
            }
        });

        window.submitClassForm = function () {
            const form = document.getElementById('class-form');
            if (form) {
                form.submit();
            }
        };

        function openAddModal() {
            document.getElementById('class-modal-title').textContent = 'Tambah Kelas';
            document.getElementById('class-form').action = "{{ route('kelas.store') }}";
            document.getElementById('class-method').value = 'POST';
            document.getElementById('class-form').reset();
            openModal('modal-class');
        }

        function editClass(id, name, wali, teacherId) {
            document.getElementById('class-modal-title').textContent = 'Edit Kelas';
            document.getElementById('class-form').action = '/kelas/' + id;
            document.getElementById('class-method').value = 'PUT';
            document.getElementById('class-name').value = name;
            document.getElementById('class-wali').value = wali;
            document.getElementById('class-teacher').value = teacherId;
            openModal('modal-class');
        }

        function openDeleteModal(id, name) {
            document.getElementById('form-delete-class').action = '/kelas/' + id;
            document.getElementById('delete-class-name').textContent = name;
            openModal('modal-delete-class');
        }

        // Close dropdown / modal if clicked outside
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => {
                if (e.target === m) m.classList.remove('open');
            });
        });

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