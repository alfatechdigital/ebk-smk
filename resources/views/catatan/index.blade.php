@extends('layouts.app')
@section('title', 'Catatan Konseling')
@section('page-title', 'Catatan Konseling')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-wrapper.single .ts-control {
        padding: 9px 14px !important;
        border: 2px solid var(--cream-dark) !important;
        border-radius: var(--radius-sm) !important;
        font-size: 13px !important;
        font-family: 'DM Sans', sans-serif !important;
        color: var(--charcoal) !important;
        background-color: #fff !important;
        min-width: 220px;
    }
    .ts-wrapper.single.focus .ts-control {
        border-color: var(--teal) !important;
        box-shadow: none !important;
    }
    .ts-dropdown {
        font-size: 13px !important;
        font-family: 'DM Sans', sans-serif !important;
        border-color: var(--cream-dark) !important;
        border-radius: var(--radius-sm) !important;
    }
</style>
@endpush

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Catatan Konseling</h2>
            <p>Dokumentasi sesi konseling dengan siswa</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-catatan-manual')"><i class="fas fa-plus"></i> Tambah Catatan Manual</button>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('catatan.index') }}">
        <select id="filter-type" onchange="toggleFilterType(this.value)" style="width: auto;">
            <option value="month" {{ request('date') ? '' : 'selected' }}>Bulan</option>
            <option value="date" {{ request('date') ? 'selected' : '' }}>Tanggal</option>
        </select>
        <input type="month" name="month" id="filter-month" value="{{ request('month') }}" onchange="this.form.submit()" style="display: {{ request('date') ? 'none' : 'inline-block' }};">
        <input type="date" name="date" id="filter-date" value="{{ request('date') }}" onchange="this.form.submit()" style="display: {{ request('date') ? 'inline-block' : 'none' }};">
        <select name="class_id" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            @foreach($classes as $c)
                <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="service_id" onchange="this.form.submit()">
            <option value="">Semua Layanan</option>
            @foreach($services as $s)
                <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <select name="student_id" id="student-select" onchange="this.form.submit()">
            <option value="">Semua Siswa</option>
            @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->user->name }}</option>
            @endforeach
        </select>
        <div style="margin-left: auto; display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 13px; color: var(--slate); font-weight: 500;">Tampilkan:</span>
                <select name="per_page" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
                    @foreach([5, 10, 25, 50, 100] as $p)
                        <option value="{{ $p }}" {{ request('per_page', 25) == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" formaction="{{ route('catatan.rekap') }}" class="btn btn-primary" style="margin: 0;"><i
                    class="fas fa-file-pdf"></i> Cetak Rekap PDF</button>
        </div>
    </form>

    <div class="catatan-grid">
        @forelse ($notes as $note)
            <div class="catatan-item">
                <div class="catatan-date">
                    <div class="day">{{ $note->created_at->format('d') }}</div>
                    <div class="month">{{ $note->created_at->format('M') }}</div>
                </div>
                <div class="catatan-content">
                    <h4>{{ $note->title }}</h4>
                    <div class="siswa" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ $note->ticket?->student?->user?->name ?? 'Anonim' }} · {{ $note->ticket?->student?->class?->name ?? '' }}</span>
                        @if($note->ticket?->service)
                            <span class="badge" style="background: {{ $note->ticket->service->color ?? 'var(--teal)' }}; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                                <i class="{{ $note->ticket->service->icon ?? 'fas fa-tag' }}"></i> {{ $note->ticket->service->name }}
                            </span>
                        @endif
                    </div>
                    <div class="catatan-label">Masalah</div>
                    <p>{{ $note->masalah }}</p>
                    <div class="catatan-label" style="margin-top:8px">Tindakan</div>
                    <p>{{ $note->tindakan }}</p>
                </div>
                <div class="action-btns" style="flex-direction: column; gap: 6px;">
                    <button class="btn btn-secondary btn-sm" style="background: var(--teal); color: white; border-color: var(--teal);" title="Detail Catatan" onclick="openNoteDetailModal(this)"
                        data-siswa="{{ $note->ticket?->student?->user?->name ? ($note->ticket->student->user->name . ' · ' . ($note->ticket->student->class->name ?? '')) : 'Anonim' }}"
                        data-title="{{ $note->title }}"
                        data-masalah="{{ $note->masalah }}"
                        data-tindakan="{{ $note->tindakan }}"
                        data-kesimpulan="{{ $note->kesimpulan ?? '-' }}"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-secondary btn-sm" title="Edit Catatan" onclick="openEditModal(this)"
                        data-id="{{ $note->id }}"
                        data-title="{{ $note->title }}"
                        data-masalah="{{ $note->masalah }}"
                        data-tindakan="{{ $note->tindakan }}"
                        data-kesimpulan="{{ $note->kesimpulan ?? '' }}"
                        data-service-id="{{ $note->ticket?->service_id ?? '' }}"><i class="fas fa-edit"></i></button>
                    <a href="{{ route('catatan.pdf', $note) }}" class="btn btn-primary btn-sm" title="Cetak Catatan"><i class="fas fa-print"></i></a>
                    <button type="button" class="btn btn-danger btn-sm" title="Hapus Catatan" onclick="openDeleteModal({{ $note->id }})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        @empty
            <div class="empty-state"><i class="fas fa-notes-medical"></i>
                <p>Belum ada catatan konseling</p>
            </div>
        @endforelse
    </div>
    {{ $notes->links('vendor.pagination.custom') }}
@endsection

@push('modals')
    <div class="modal-overlay" id="modal-catatan">
        <div class="modal">
            <div class="modal-header">
                <h3>Tambah Catatan Konseling</h3><button class="modal-close"
                    onclick="closeModal('modal-catatan')">✕</button>
            </div>
            <form method="POST" action="{{ route('catatan.store') }}">
                @csrf
                <div class="field-group">
                    <label>Tiket Terkait</label>
                    <select name="ticket_id" required>
                        <option value="">Pilih tiket...</option>
                        @php
                            $teacherTickets = \App\Models\Ticket::with('student.user')
                                ->where('teacher_id', auth()->user()->teacher?->id)
                                ->whereIn('status', ['diproses', 'menunggu'])
                                ->get();
                        @endphp
                        @foreach($teacherTickets as $t)
                            <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->student?->user?->name ?? 'Anonim' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group"><label>Judul</label><input type="text" name="title" placeholder="Judul catatan"
                        required></div>
                <div class="field-group"><label>Masalah / Permasalahan</label><textarea name="masalah"
                        placeholder="Deskripsikan masalah yang dihadapi siswa..." required></textarea></div>
                <div class="field-group"><label>Tindakan yang Dilakukan</label><textarea name="tindakan"
                        placeholder="Tindakan, teknik, atau intervensi..." required></textarea></div>
                <div class="field-group"><label>Kesimpulan (opsional)</label><textarea name="kesimpulan"
                        placeholder="Kesimpulan sesi konseling..."></textarea></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-catatan')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>

{{-- Modal Edit Catatan --}}
<div class="modal-overlay" id="modal-edit-catatan">
    <div class="modal" style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Edit Catatan Konseling</h3><button class="modal-close" onclick="closeModal('modal-edit-catatan')">✕</button>
        </div>
        <form method="POST" id="form-edit-catatan">
            @csrf
            @method('PUT')
            <div class="field-group"><label>Judul</label><input type="text" name="title" id="edit-title" required></div>
            <div class="field-group">
                <label>Jenis Layanan</label>
                <select name="service_id" id="edit-service-id" required>
                    <option value="">Pilih layanan...</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group"><label>Masalah / Permasalahan</label><textarea name="masalah" id="edit-masalah" required></textarea></div>
            <div class="field-group"><label>Tindakan yang Dilakukan</label><textarea name="tindakan" id="edit-tindakan" required></textarea></div>
            <div class="field-group"><label>Kesimpulan (opsional)</label><textarea name="kesimpulan" id="edit-kesimpulan"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-catatan')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Catatan Manual --}}
<div class="modal-overlay" id="modal-catatan-manual">
    <div class="modal" style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Tambah Catatan Manual</h3>
            <button class="modal-close" onclick="closeModal('modal-catatan-manual')">✕</button>
        </div>
        <form method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <div class="field-group">
                <label>Nama Siswa</label>
                <select name="student_id" id="manual-student-select" required>
                    <option value="">Pilih siswa...</option>
                    @foreach($allStudents as $s)
                        <option value="{{ $s->id }}">{{ $s->class->name ?? '-' }} - {{ $s->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group">
                <label>Jenis Layanan</label>
                <select name="service_id" required>
                    <option value="">Pilih layanan...</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group">
                <label>Tanggal</label>
                <input type="date" name="created_at" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="field-group">
                <label>Judul</label>
                <input type="text" name="title" placeholder="Judul catatan" required>
            </div>
            <div class="field-group">
                <label>Masalah / Permasalahan</label>
                <textarea name="masalah" placeholder="Deskripsikan masalah..." required></textarea>
            </div>
            <div class="field-group">
                <label>Tindakan yang Dilakukan</label>
                <textarea name="tindakan" placeholder="Tindakan, teknik, atau intervensi..." required></textarea>
            </div>
            <div class="field-group">
                <label>Kesimpulan (opsional)</label>
                <textarea name="kesimpulan" placeholder="Kesimpulan sesi konseling..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-catatan-manual')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div class="modal-overlay" id="modal-delete-catatan">
    <div class="modal" style="max-width: 400px; text-align: center;">
        <div style="font-size: 3rem; color: var(--danger); margin-bottom: 15px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3>Hapus Catatan Konseling?</h3>
        <p style="color: #666; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
            Apakah Anda yakin ingin menghapus catatan ini? Tindakan ini akan menghapus catatan secara permanen dan tidak dapat dibatalkan.
        </p>
        <form method="POST" id="form-delete-catatan" style="margin-top: 25px;">
            @csrf
            @method('DELETE')
            <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 0;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-catatan')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Catatan --}}
<div class="modal-overlay" id="modal-detail-catatan">
    <div class="modal" style="max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fa-solid fa-file-lines" style="color: var(--teal);"></i> Detail Catatan Konseling
            </h3>
            <button class="modal-close" onclick="closeModal('modal-detail-catatan')">✕</button>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Siswa</label>
                <div id="detail-siswa" style="font-size: 0.95rem; color: var(--navy); font-weight: 700;">-</div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Judul / Topik</label>
                <div id="detail-title" style="font-size: 0.95rem; color: var(--navy); font-weight: 700;">-</div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Masalah / Permasalahan</label>
                <div id="detail-masalah" style="font-size: 0.9rem; color: #334155; background: #f8fafc; border-left: 4px solid var(--teal); padding: 12px; border-radius: 0 8px 8px 0; line-height: 1.5; white-space: pre-wrap; word-break: break-word;">-</div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Tindakan yang Dilakukan (Solusi)</label>
                <div id="detail-tindakan" style="font-size: 0.9rem; color: #334155; background: #f8fafc; border-left: 4px solid #059669; padding: 12px; border-radius: 0 8px 8px 0; line-height: 1.5; white-space: pre-wrap; word-break: break-word;">-</div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Kesimpulan</label>
                <div id="detail-kesimpulan" style="font-size: 0.9rem; color: #334155; background: #f8fafc; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 0 8px 8px 0; line-height: 1.5; white-space: pre-wrap; word-break: break-word;">-</div>
            </div>
        </div>

        <div class="modal-footer" style="justify-content: flex-end; gap: 10px; border-top: none; padding-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-catatan')">Tutup</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open') }
        function closeModal(id) { document.getElementById(id).classList.remove('open') }
        document.querySelectorAll('.modal-overlay').forEach(m => { m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open') }) });

        function toggleFilterType(type) {
            const monthInput = document.getElementById('filter-month');
            const dateInput = document.getElementById('filter-date');
            if (type === 'month') {
                monthInput.style.display = 'inline-block';
                dateInput.style.display = 'none';
                dateInput.value = '';
            } else {
                monthInput.style.display = 'none';
                dateInput.style.display = 'inline-block';
                monthInput.value = '';
            }
        }

        function openNoteDetailModal(btn) {
            document.getElementById('detail-siswa').innerText = btn.getAttribute('data-siswa');
            document.getElementById('detail-title').innerText = btn.getAttribute('data-title');
            document.getElementById('detail-masalah').innerText = btn.getAttribute('data-masalah');
            document.getElementById('detail-tindakan').innerText = btn.getAttribute('data-tindakan');
            document.getElementById('detail-kesimpulan').innerText = btn.getAttribute('data-kesimpulan') || '-';
            openModal('modal-detail-catatan');
        }

        function openEditModal(btn) {
            const id = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const masalah = btn.getAttribute('data-masalah');
            const tindakan = btn.getAttribute('data-tindakan');
            const kesimpulan = btn.getAttribute('data-kesimpulan');
            const serviceId = btn.getAttribute('data-service-id');

            document.getElementById('form-edit-catatan').action = '/catatan/' + id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-masalah').value = masalah;
            document.getElementById('edit-tindakan').value = tindakan;
            document.getElementById('edit-kesimpulan').value = kesimpulan || '';
            const serviceSelect = document.getElementById('edit-service-id');
            if (serviceSelect) {
                serviceSelect.value = serviceId || '';
            }
            openModal('modal-edit-catatan');
        }

        function openDeleteModal(id) {
            document.getElementById('form-delete-catatan').action = '/catatan/' + id;
            openModal('modal-delete-catatan');
        }

        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect('#student-select', {
                create: false,
                placeholder: 'Cari nama siswa...',
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            new TomSelect('#manual-student-select', {
                create: false,
                placeholder: 'Cari nama siswa...',
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
@endpush