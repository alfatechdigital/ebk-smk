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

        .catatan-item {
            align-items: center !important;
        }
    </style>
@endpush

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Catatan Konseling</h2>
            <p>Dokumentasi sesi konseling dengan siswa</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('modal-catatan-manual')"><i class="fas fa-plus"></i> Tambah
            Catatan Manual</button>
    </div>

    <form class="filter-bar" method="GET" action="{{ route('catatan.index') }}">
        <select id="filter-type" onchange="toggleFilterType(this.value)" style="width: auto;">
            <option value="month" {{ request('date') ? '' : 'selected' }}>Bulan</option>
            <option value="date" {{ request('date') ? 'selected' : '' }}>Tanggal</option>
        </select>
        <input type="month" name="month" id="filter-month" value="{{ request('month') }}" onchange="this.form.submit()"
            style="display: {{ request('date') ? 'none' : 'inline-block' }};">
        <input type="date" name="date" id="filter-date" value="{{ request('date') }}" onchange="this.form.submit()"
            style="display: {{ request('date') ? 'inline-block' : 'none' }};">
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
        <select name="student_name" id="student-select" onchange="this.form.submit()">
            <option value="">Semua Siswa</option>
            @foreach($studentNames as $name)
                <option value="{{ $name }}" {{ request('student_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
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
            <div class="rekap-dropdown-wrapper" style="position: relative; display: inline-block;">
                <button type="button" class="btn btn-primary"
                    style="margin: 0; display: inline-flex; align-items: center; gap: 6px;"
                    onclick="toggleRekapDropdown(event)">
                    <i class="fas fa-download"></i> Ekspor Rekap <i class="fas fa-chevron-down"
                        style="font-size: 10px;"></i>
                </button>
                <div class="rekap-dropdown-menu" id="rekap-dropdown-menu"
                    style="display: none; position: absolute; right: 0; top: calc(100% + 5px); background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 100; min-width: 140px; overflow: hidden; text-align: left;">
                    <button type="submit" formaction="{{ route('catatan.rekap') }}"
                        style="display: flex; width: 100%; border: none; background: transparent; align-items: center; gap: 8px; padding: 10px 14px; font-size: 12px; color: #334155; text-decoration: none; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-file-pdf" style="color: #ef4444; width: 14px;"></i> Cetak PDF
                    </button>
                    <button type="submit" formaction="{{ route('catatan.rekap') }}" name="format" value="word"
                        style="display: flex; width: 100%; border: none; background: transparent; align-items: center; gap: 8px; padding: 10px 14px; font-size: 12px; color: #334155; text-decoration: none; cursor: pointer; transition: background 0.2s; border-top: 1px solid #f1f5f9;"
                        onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-file-word" style="color: #2b579a; width: 14px;"></i> Ekspor Word
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="catatan-grid">
        @forelse ($notes as $note)
            <div class="catatan-item">
                <div class="catatan-date">
                    <div class="day">{{ $note->created_at->format('d') }}</div>
                    <div class="month">{{ $note->created_at->locale('id')->translatedFormat('M') }}</div>
                    <div style="font-size: 10px; font-weight: 700; opacity: 0.8; margin-top: 2px; line-height: 1;">
                        {{ $note->created_at->format('Y') }}</div>
                </div>
                <div class="catatan-content"
                    style="min-width: 0; display: flex; flex-direction: column; gap: 4px; justify-content: center; flex-grow: 1;">
                    <div class="siswa" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <i class="fas fa-user-circle" style="color: var(--slate); font-size: 13px;"></i>
                        <span
                            style="font-size: 13px; color: var(--slate); font-weight: 600;">{{ $note->ticket?->student_name ?? $note->ticket?->student?->user?->name ?? 'Anonim' }}
                            · {{ $note->ticket?->class_name ?? $note->ticket?->class?->name ?? $note->ticket?->student?->class?->name ?? '' }}</span>
                        @if($note->ticket?->service)
                            <span class="badge"
                                style="background: {{ $note->ticket->service->color ?? 'var(--teal)' }}; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; line-height: 1.2;">
                                <i
                                    class="fas {{ str_starts_with($note->ticket->service->icon ?? 'fa-tag', 'fas ') ? Str::after($note->ticket->service->icon, 'fas ') : ($note->ticket->service->icon ?? 'fa-tag') }}"></i>
                                {{ $note->ticket->service->name }}
                            </span>
                        @endif
                    </div>
                    <h4
                        style="margin: 0; font-size: 14px; font-weight: 700; color: var(--charcoal); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $note->title }}</h4>
                    <div style="margin: 0; font-size: 13px; color: var(--slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        title="{{ $note->masalah }}">{{ $note->masalah }}</div>
                </div>
                <div class="action-btns"
                    style="display: flex; flex-direction: row; align-items: center; gap: 8px; flex-shrink: 0; margin-left: auto;">
                    <button class="btn btn-secondary btn-sm"
                        style="background: var(--teal); color: white; border-color: var(--teal); margin: 0; display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px; font-weight: 600;"
                        title="Detail Catatan" onclick="openNoteDetailModal(this)"
                        data-nama="{{ $note->ticket?->student_name ?? $note->ticket?->student?->user?->name ?? 'Anonim' }}"
                        data-kelas="{{ $note->ticket?->class?->name ?? $note->ticket?->student?->class?->name ?? '-' }}"
                        data-layanan="{{ $note->ticket?->service?->name ?? '-' }}"
                        data-layanan-color="{{ $note->ticket?->service?->color ?? 'var(--teal)' }}"
                        data-layanan-icon="fas {{ str_starts_with($note->ticket?->service?->icon ?? 'fa-tag', 'fas ') ? Str::after($note->ticket->service->icon, 'fas ') : ($note->ticket?->service?->icon ?? 'fa-tag') }}"
                        data-guru="{{ $note->teacher?->user?->name ?? '-' }}"
                        data-tanggal="{{ $note->created_at->translatedFormat('d M Y') }}" data-title="{{ $note->title }}"
                        data-masalah="{{ $note->masalah }}" data-tindakan="{{ $note->tindakan }}"
                        data-kesimpulan="{{ $note->kesimpulan ?? '-' }}" data-ticket-id="{{ $note->ticket_id ?? '' }}">
                        <i class="fas fa-info-circle"></i> Detail
                    </button>

                    <div class="ticket-actions-dropdown" style="position: relative; display: inline-block;">
                        <button type="button" class="btn btn-secondary btn-sm dropdown-trigger"
                            style="padding: 6px 10px; font-size: 12px; margin: 0; display: inline-flex; align-items: center; justify-content: center; height: 28px; background: transparent; border: 1px solid var(--slate-light, #cbd5e1); color: var(--slate);"
                            onclick="toggleNoteActionsDropdown(event, '{{ $note->id }}')">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu-content" id="note-actions-dropdown-{{ $note->id }}"
                            style="display: none; position: absolute; right: 0; top: calc(100% + 5px); background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); z-index: 100; min-width: 170px; overflow: hidden; text-align: left;">
                            <a href="#"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: #334155; text-decoration: none; transition: background 0.2s;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'"
                                onclick="event.preventDefault(); closeAllDropdowns(); openEditModalFromData('{{ $note->id }}', '{{ addslashes($note->title) }}', '{{ addslashes($note->masalah) }}', '{{ addslashes($note->tindakan) }}', '{{ addslashes($note->kesimpulan ?? '') }}', '{{ $note->ticket?->service_id ?? '' }}')">
                                <i class="fas fa-edit" style="width: 14px; color: var(--slate);"></i> Edit Catatan
                            </a>
                            <a href="{{ route('catatan.pdf', $note) }}"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: #334155; text-decoration: none; transition: background 0.2s; border-top: 1px solid #f1f5f9;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <i class="fas fa-file-pdf" style="width: 14px; color: #ef4444;"></i> Cetak PDF
                            </a>
                            <a href="{{ route('catatan.pdf', [$note, 'format' => 'word']) }}"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: #334155; text-decoration: none; transition: background 0.2s; border-top: 1px solid #f1f5f9;"
                                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <i class="fas fa-file-word" style="width: 14px; color: #2b579a;"></i> Ekspor Word
                            </a>
                            <a href="#"
                                style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; color: #ef4444; text-decoration: none; transition: background 0.2s; border-top: 1px solid #f1f5f9;"
                                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
                                onclick="event.preventDefault(); closeAllDropdowns(); openDeleteModal('{{ $note->id }}')">
                                <i class="fas fa-trash" style="width: 14px; color: #ef4444;"></i> Hapus Catatan
                            </a>
                        </div>
                    </div>
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
                            <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->student_name ?? $t->student?->user?->name ?? 'Anonim' }}</option>
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
                <h3>Edit Catatan Konseling</h3><button class="modal-close"
                    onclick="closeModal('modal-edit-catatan')">✕</button>
            </div>
            <form method="POST" id="form-edit-catatan">
                @csrf
                @method('PUT')
                <div class="field-group"><label>Judul</label><input type="text" name="title" id="edit-title" required></div>
                <div class="field-group">
                    <label>Kategori Layanan</label>
                    <select name="service_id" id="edit-service-id" required>
                        <option value="">Pilih layanan...</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group"><label>Masalah / Permasalahan</label><textarea name="masalah" id="edit-masalah"
                        required></textarea></div>
                <div class="field-group"><label>Tindakan yang Dilakukan</label><textarea name="tindakan" id="edit-tindakan"
                        required></textarea></div>
                <div class="field-group"><label>Kesimpulan (opsional)</label><textarea name="kesimpulan"
                        id="edit-kesimpulan"></textarea></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modal-edit-catatan')">Batal</button>
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
                    <label>Kategori Layanan</label>
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
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modal-catatan-manual')">Batal</button>
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
                Apakah Anda yakin ingin menghapus catatan ini? Tindakan ini akan menghapus catatan secara permanen dan tidak
                dapat dibatalkan.
            </p>
            <form method="POST" id="form-delete-catatan" style="margin-top: 25px;">
                @csrf
                @method('DELETE')
                <div class="modal-footer" style="justify-content: center; gap: 10px; border-top: none; padding-top: 0;">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modal-delete-catatan')">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Edit --}}
    <div class="modal-overlay" id="modal-confirm-edit-catatan">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Simpan Perubahan?</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menyimpan perubahan pada catatan konseling ini?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-edit-catatan')"
                    style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitEditForm()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya,
                    Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal Detail Catatan --}}
    {{-- Modal Detail Catatan --}}
    <div class="modal-overlay" id="modal-detail-catatan">
        <div class="modal" style="max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px;">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h3
                    style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                    <i class="fa-solid fa-file-lines" style="color: var(--teal);"></i> Detail Catatan Layanan BK
                </h3>
                <button class="modal-close" onclick="closeModal('modal-detail-catatan')">✕</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Grid Identitas & Layanan -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: left;">
                    <div>
                        <label
                            style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Siswa</label>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-siswa-nama">-</div>
                    </div>
                    <div>
                        <label
                            style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kelas</label>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-siswa-kelas">-</div>
                    </div>
                    <div>
                        <label
                            style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Guru
                            BK</label>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-siswa-guru">-</div>
                    </div>
                    <div>
                        <label
                            style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Tanggal
                            Konseling</label>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-siswa-tanggal">-</div>
                    </div>
                    <div>
                        <label
                            style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kategori
                            Layanan</label>
                        <div>
                            <span id="detail-siswa-layanan" class="badge"
                                style="color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 6px;">-</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Masalah -->
                <div style="text-align: left;">
                    <label
                        style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Topik
                        Konsultasi</label>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;" id="detail-title"></div>
                </div>

                <div style="text-align: left;">
                    <label
                        style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Masalah
                        / Permasalahan</label>
                    <div style="font-size: 0.925rem; color: #334155; background: #f8fafc; border-left: 4px solid var(--teal); padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;"
                        id="detail-masalah"></div>
                </div>

                <div style="text-align: left;">
                    <label
                        style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Tindakan
                        yang Dilakukan (Solusi)</label>
                    <div style="font-size: 0.925rem; color: #334155; background: #f8fafc; border-left: 4px solid #059669; padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;"
                        id="detail-tindakan"></div>
                </div>

                <div style="text-align: left;">
                    <label
                        style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Kesimpulan</label>
                    <div style="font-size: 0.925rem; color: #334155; background: #f8fafc; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;"
                        id="detail-kesimpulan"></div>
                </div>
            </div>

            <div class="modal-footer"
                style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: flex-end; position: relative;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-catatan')"
                    style="margin: 0;">Tutup</button>
                <a href="#" id="detail-note-chat-btn" class="btn btn-primary"
                    style="margin: 0; display: none; align-items: center; gap: 4px;"><i class="fas fa-comments"></i> Riwayat
                    Percakapan</a>
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
            document.getElementById('detail-siswa-nama').innerText = btn.getAttribute('data-nama');
            document.getElementById('detail-siswa-kelas').innerText = btn.getAttribute('data-kelas');
            document.getElementById('detail-siswa-guru').innerText = btn.getAttribute('data-guru');
            document.getElementById('detail-siswa-tanggal').innerText = btn.getAttribute('data-tanggal');

            const badge = document.getElementById('detail-siswa-layanan');
            if (badge) {
                badge.style.background = btn.getAttribute('data-layanan-color');
                badge.innerHTML = `<i class="${btn.getAttribute('data-layanan-icon')}"></i> ${btn.getAttribute('data-layanan')}`;
            }

            document.getElementById('detail-title').innerText = btn.getAttribute('data-title');
            document.getElementById('detail-masalah').innerText = btn.getAttribute('data-masalah');
            document.getElementById('detail-tindakan').innerText = btn.getAttribute('data-tindakan');
            document.getElementById('detail-kesimpulan').innerText = btn.getAttribute('data-kesimpulan') || '-';

            // Check ticket-id to show/hide Chat button
            const ticketId = btn.getAttribute('data-ticket-id');
            const chatBtn = document.getElementById('detail-note-chat-btn');
            if (chatBtn) {
                if (ticketId && ticketId.trim() !== '') {
                    chatBtn.href = '/chat/' + ticketId;
                    chatBtn.style.display = 'inline-flex';
                } else {
                    chatBtn.style.display = 'none';
                    chatBtn.href = '#';
                }
            }

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

        document.addEventListener("DOMContentLoaded", function () {
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

            // Intercept edit form submission to show confirmation prompt
            const editForm = document.getElementById('form-edit-catatan');
            if (editForm) {
                editForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-edit-catatan');
                });
            }
        });

        window.submitEditForm = function () {
            const editForm = document.getElementById('form-edit-catatan');
            if (editForm) {
                editForm.submit();
            }
        };

        window.closeAllDropdowns = function () {
            const allMenus = document.querySelectorAll('.dropdown-menu-content');
            allMenus.forEach(menu => {
                menu.style.display = 'none';
            });
        };

        window.toggleNoteActionsDropdown = function (event, id) {
            event.stopPropagation();
            const dropdown = document.getElementById('note-actions-dropdown-' + id);
            const isOpen = dropdown && dropdown.style.display === 'block';

            // Close other dropdowns first
            closeAllDropdowns();
            const rekapMenu = document.getElementById('rekap-dropdown-menu');
            if (rekapMenu) rekapMenu.style.display = 'none';

            if (dropdown && !isOpen) {
                dropdown.style.display = 'block';
            }
        };

        window.toggleRekapDropdown = function (event) {
            event.stopPropagation();

            // Close all note dropdowns
            closeAllDropdowns();

            const dropdown = document.getElementById('rekap-dropdown-menu');
            if (dropdown) {
                if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }
        };

        window.openEditModalFromData = function (id, title, masalah, tindakan, kesimpulan, serviceId) {
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
        };

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.ticket-actions-dropdown')) {
                closeAllDropdowns();
            }
            if (!event.target.closest('.rekap-dropdown-wrapper')) {
                const rekapMenu = document.getElementById('rekap-dropdown-menu');
                if (rekapMenu) rekapMenu.style.display = 'none';
            }
        });
    </script>
@endpush