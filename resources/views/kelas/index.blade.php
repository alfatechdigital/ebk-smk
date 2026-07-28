@extends('layouts.app')
@section('title', 'Manajemen Kelas')
@section('page-title', 'Manajemen Kelas')

@push('styles')
    <style>
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
    <div class="page-header-row">
        <div class="page-header">
            <h2>Manajemen Kelas</h2>
            <p>Kelola daftar kelas dan pembagian guru BK pengampu.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <button class="btn btn-secondary" onclick="openImportModal()"
                style="background: #ffffff; color: var(--slate); border: 1px solid #cbd5e1; font-weight: 500;"><i
                    class="fas fa-file-excel" style="color: #10b981; margin-right: 4px;"></i> Import Kelas</button>
            <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah Kelas</button>
        </div>
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
    {{-- Modal Import Kelas --}}
    <div class="modal-overlay" id="modal-import-kelas">
        <div class="modal" style="max-width: 500px; width: 95%;">
            <div class="modal-header">
                <h3>Import Data Kelas</h3>
                <button class="modal-close" onclick="closeModal('modal-import-kelas')">✕</button>
            </div>
            <form id="form-import-kelas" method="POST" action="{{ route('kelas.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="force_update" id="import-force-update" value="0">
                <div class="form-row" style="margin-bottom: 20px;">
                    <div class="field-group" style="width: 100%;">
                        <label style="display: block; font-size: 0.9rem; font-weight: 600; color: var(--charcoal); margin-bottom: 8px;">File Excel (.xlsx, .xls)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            style="padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; width: 100%; background: #f8f9fa;">
                    </div>
                </div>
                <a href="/E-BK_Template_Import_Kelas.xlsx" download="E-BK_Template_Import_Kelas.xlsx" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;"><i class="fas fa-download"></i>Unduh Template</a>
                <div style="margin-top: 15px; font-size: 0.9rem; color: #475569; background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                    <p style="margin-bottom: 8px; font-weight: 600; color: #1e3a8a;"><i class="fas fa-info-circle"></i> Panduan Proses Import Data Kelas:</p>
                    <ol style="margin-left: 20px; line-height: 1.5; display: flex; flex-direction: column; gap: 4px;">
                        <li>Unduh template file Excel melalui tombol <strong>"Unduh Template"</strong> di atas.</li>
                        <li>Buka file template tersebut dan isi data kelas baru sesuai kolom yang disediakan (Kelas, Wali Kelas, Guru BK).</li>
                        <li>Pastikan nama Guru BK pada kolom <strong>Guru BK</strong> sesuai dengan nama Guru yang terdaftar di sistem.</li>
                        <li>Klik tombol <strong>"Import"</strong> di bawah untuk memulai proses unggah data kelas.</li>
                    </ol>
                </div>
                <div class="modal-footer" style="margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-import-kelas')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Duplikasi Kelas saat Import --}}
    <div class="modal-overlay" id="modal-confirm-import-kelas-duplicate" style="z-index: 1100;">
        <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Duplikasi Kelas Terdeteksi</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Terdapat <strong id="import-duplicate-count">0</strong> kelas dengan nama yang sama sudah terdaftar di database.
                <br><br>
                Apakah Anda ingin melanjutkan dan <strong>memperbarui</strong> data kelas tersebut (Wali Kelas & Guru BK) dengan data terbaru dari Excel, atau membatalkan proses import?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-import-kelas-duplicate')"
                    style="margin: 0; min-width: 120px;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="proceedImportClassWithUpdate()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal); min-width: 120px;">Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- Loading Overlay for Class Import --}}
    <div class="modal-overlay" id="modal-import-kelas-loading" style="z-index: 1200; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 32px; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="margin-bottom: 20px; display: flex; justify-content: center;">
                <div class="premium-spinner"></div>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">Mengimport Data Kelas</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Sedang memproses dan menyimpan data kelas ke dalam database. Mohon jangan menutup halaman ini...
            </p>
        </div>
    </div>

    @if(request('import_success'))
        <div class="modal-overlay open" id="modal-success-notification">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--success, #10b981); margin-bottom: 15px;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Berhasil</h3>
                <p style="color: #64748b; font-size: 0.95rem; margin-top: 10px; line-height: 1.5;">
                    Seluruh data kelas berhasil diimport dengan sukses ke dalam database.
                </p>
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-primary" onclick="closeModal('modal-success-notification')"
                        style="margin: 0; min-width: 120px;">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    @if(request('import_error'))
        <div class="modal-overlay open" id="modal-import-error">
            <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
                <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Import Gagal</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                    {{ request('import_error') }}
                </p>
                <div class="modal-footer"
                    style="justify-content: center; border-top: none; padding-top: 20px; margin-top: 10px;">
                    <button type="button" class="btn btn-danger" onclick="closeModal('modal-import-error')"
                        style="margin: 0; background: var(--danger); border-color: var(--danger); color: #fff; min-width: 120px;">Tutup</button>
                </div>
            </div>
        </div>
    @endif
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }

        document.addEventListener("DOMContentLoaded", function () {
            // Clean up import query parameters from address bar to prevent modal popup on refresh
            if (window.location.search.includes('import_success=1') || window.location.search.includes('import_error=')) {
                const url = new URL(window.location);
                url.searchParams.delete('import_success');
                url.searchParams.delete('import_error');
                window.history.replaceState({}, document.title, url.toString());
            }

            const form = document.getElementById('class-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-class');
                });
            }

            const importForm = document.getElementById('form-import-kelas');
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

                    fetch("{{ route('kelas.import-check') }}", {
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
                                openModal('modal-confirm-import-kelas-duplicate');
                            } else {
                                forceUpdateInput.value = '0';
                                closeModal('modal-import-kelas');
                                openModal('modal-import-kelas-loading');
                                importForm.submit();
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                            closeModal('modal-import-kelas');
                            openModal('modal-import-kelas-loading');
                            importForm.submit();
                        });
                });
            }
        });

        window.submitClassForm = function () {
            const form = document.getElementById('class-form');
            if (form) {
                form.submit();
            }
        };

        window.openImportModal = function () {
            openModal('modal-import-kelas');
        };

        window.proceedImportClassWithUpdate = function () {
            document.getElementById('import-force-update').value = '1';
            closeModal('modal-confirm-import-kelas-duplicate');
            closeModal('modal-import-kelas');
            openModal('modal-import-kelas-loading');
            document.getElementById('form-import-kelas').submit();
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