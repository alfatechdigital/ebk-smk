@extends('layouts.app')
@section('title', 'Tiket Konsultasi')
@section('page-title', auth()->user()->isSiswa() ? 'Konsultasi Saya' : 'Manajemen Tiket')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        @if(auth()->user()->isSiswa())
            <h2>Konsultasi Saya</h2>
            <p>Kelola tiket konsultasi kamu</p>
        @else
            <h2>Manajemen Tiket</h2>
            <p>Daftar semua tiket konsultasi siswa</p>
        @endif
    </div>
    @if(auth()->user()->isSiswa())
        <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan Konsultasi</a>
    @elseif(auth()->user()->isAdmin())
        <button class="btn btn-primary" onclick="openModal('modal-ticket')"><i class="fas fa-plus"></i> Tambah Tiket</button>
    @endif
</div>

@if(auth()->user()->isSiswa())
<div class="warning-box mb-20"><i class="fas fa-lock"></i><span>Semua konsultasi bersifat <b>rahasia</b>. Hanya kamu dan Guru BK yang dapat melihat isi percakapan.</span></div>
@endif

<!-- Filter -->
<form class="filter-bar" method="GET">
    <input type="text" name="search" id="search-input" placeholder="Cari nama siswa atau judul" value="{{ request('search') }}" autocomplete="off">
    <input type="hidden" name="favorite" id="filter-favorite-input" value="{{ request('favorite') }}">
    <input type="hidden" name="anonymous" id="filter-anonymous-input" value="{{ request('anonymous') }}">

    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="menunggu" {{ request('status')=='menunggu'?'selected':'' }}>Menunggu</option>
        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
        <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
        <option value="dibatalkan" {{ request('status')=='dibatalkan'?'selected':'' }}>Dibatalkan</option>
    </select>
    <select name="service" onchange="this.form.submit()">
        <option value="">Semua Layanan</option>
        @foreach($services as $s)
            <option value="{{ $s->id }}" {{ request('service')==$s->id?'selected':'' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="unread" onchange="this.form.submit()">
        <option value="">Semua Pesan</option>
        <option value="1" {{ request('unread')=='1'?'selected':'' }}>Belum Dibaca</option>
    </select>

    <div class="filter-chips" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
        <button type="button" class="filter-chip {{ request('favorite') == '1' ? 'active' : '' }}" onclick="toggleChip('favorite', this)" style="cursor: pointer; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid #d1d5db; background: #fff; color: var(--slate); transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; height: 38px; box-sizing: border-box;">
            <i class="fa-solid fa-star" style="font-size: 11px;"></i> Favorit
        </button>
        <button type="button" class="filter-chip {{ request('anonymous') == '1' ? 'active' : '' }}" onclick="toggleChip('anonymous', this)" style="cursor: pointer; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; border: 1px solid #d1d5db; background: #fff; color: var(--slate); transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; height: 38px; box-sizing: border-box;">
            <i class="fa-solid fa-user-secret" style="font-size: 11px;"></i> Anonim
        </button>
    </div>
    <div style="margin-left: auto; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 13px; color: var(--slate); font-weight: 500;">Tampilkan:</span>
        <select name="per_page" onchange="this.form.submit()" style="width: auto; padding: 6px 12px; margin: 0;">
            @foreach([5, 10, 25, 50, 100] as $p)
                <option value="{{ $p }}" {{ request('per_page', 25) == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
</form>

<style>
    .filter-chip.active {
        background-color: var(--teal) !important;
        color: #fff !important;
        border-color: var(--teal) !important;
    }
    .ticket-list-item {
        width: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 16px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .ticket-list-item.is-pinned {
        background-color: #fffdf5 !important;
        border-color: #f59e0b !important;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.08) !important;
    }
    .badge-menunggu {
        background-color: #fffbeb !important;
        color: #d97706 !important;
        border: 1px solid #fde68a !important;
    }
    .badge-diproses {
        background-color: #eff6ff !important;
        color: #2563eb !important;
        border: 1px solid #bfdbfe !important;
    }
    .badge-selesai {
        background-color: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .badge-dibatalkan {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        border: 1px solid #fecaca !important;
    }
    @media (max-width: 1024px) {
        .ticket-list-item {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 14px !important;
        }
        .ticket-list-item > div {
            flex: unset !important;
            width: 100% !important;
        }
        .ticket-list-item > div:last-child {
            justify-content: flex-start !important;
        }
    }
    .inline-actions-group {
        display: none;
        align-items: center;
        gap: 8px;
        margin-right: auto;
        flex-wrap: wrap;
    }
    .inline-actions-buttons {
        display: none;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    @media (min-width: 768px) {
        .inline-actions-group, .inline-actions-buttons {
            flex-wrap: nowrap !important;
        }
    }
</style>

<!-- Ticket List -->
<div id="tickets-container">
    @include('tickets.partials.list')
</div>
@endsection

@push('modals')
@if(auth()->user()->isAdmin())
<div class="modal-overlay" id="modal-ticket">
    <div class="modal">
        <div class="modal-header"><h3>Buat Tiket Baru</h3><button class="modal-close" onclick="closeModal('modal-ticket')">✕</button></div>
        <form method="POST" action="{{ route('tickets.store') }}">
            @csrf
            <div class="field-group">
                <label>Jenis Layanan</label>
                <select name="service_id" required>
                    <option value="">Pilih layanan...</option>
                    @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group">
                <label>Guru BK</label>
                <select name="teacher_id">
                    @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->user->name }}</option>@endforeach
                </select>
            </div>
            <div class="field-group"><label>Topik Konsultasi</label><input type="text" name="title" placeholder="Topik konsultasi" required></div>
            <div class="field-group"><label>Ceritakan apa yang ingin kamu konsultasikan</label><textarea name="description" placeholder="Tuliskan di sini..." required></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-ticket')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Buat Tiket</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modal Detail Masalah --}}
<div class="modal-overlay" id="modal-detail-ticket">
    <div class="modal" style="max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fas fa-file-alt" style="color: var(--teal);"></i> Rincian Masalah & Konsultasi
            </h3>
            <button class="modal-close" onclick="closeModal('modal-detail-ticket')">✕</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Grid Identitas & Layanan -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: left;">
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Siswa</label>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-student-info"></div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kelas</label>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-class-info"></div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Guru BK</label>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-teacher-info"></div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kategori Layanan</label>
                    <span id="detail-service-badge" class="badge" style="color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 6px;"></span>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kode Tiket</label>
                    <span id="detail-code" style="font-weight: 800; color: var(--teal); font-size: 0.95rem;"></span>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Tanggal Konseling</label>
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1e293b;" id="detail-date-info">-</div>
                </div>
            </div>

            {{-- Anonymous Banner (shown via JS) --}}
            @if(auth()->user()->isSiswa())
                <div id="detail-anon-banner-student" style="display: none; align-items: center; gap: 8px; background-color: #1e293b; border: 1px solid #334155; padding: 10px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                    <i class="fas fa-user-secret" style="font-size: 14px; color: #cbd5e1; flex-shrink: 0;"></i>
                    <span style="color: #cbd5e1;"><strong style="color: #e2e8f0;">Konsultasi Anonim:</strong> Kamu mengajukan konsultasi ini secara anonim. Identitasmu sepenuhnya tersembunyi dan terjaga kerahasiaannya di sistem.</span>
                </div>
            @else
                <div id="detail-anon-banner-guru" style="display: none; align-items: center; gap: 8px; background-color: #fef2f2; border: 1px solid #fee2e2; color: #dc2626; padding: 10px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                    <i class="fas fa-user-secret" style="font-size: 14px; flex-shrink: 0;"></i>
                    <span><strong>Sesi Konsultasi Anonim:</strong> Siswa mengajukan konsultasi ini secara anonim untuk menjaga kerahasiaan identitas aslinya.</span>
                </div>
            @endif

            <!-- Detail Masalah -->
            <div style="text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Topik Konsultasi</label>
                <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;" id="detail-title"></div>
            </div>

            <div style="text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Deskripsi Masalah</label>
                <div style="font-size: 0.925rem; color: #334155; background: #f8fafc; border-left: 4px solid var(--teal); padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;" id="detail-description"></div>
            </div>

            <div id="detail-cancel-reason-wrapper" style="display: none; text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px; color: #ef4444;">Alasan Pembatalan</label>
                <div style="font-size: 0.925rem; color: #ef4444; background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;" id="detail-cancel-reason"></div>
            </div>
        </div>
        <div class="modal-footer" style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: flex-end; position: relative;">
            @if(auth()->user()->isGuru())
                <div id="detail-actions-wrapper" class="inline-actions-group">
                    <button type="button" id="btn-toggle-actions" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; margin: 0; padding: 6px 12px; font-size: 12px; white-space: nowrap;" onclick="toggleInlineActions()">
                        <i class="fas fa-cogs"></i> Tindakan <i class="fas fa-chevron-right" id="actions-chevron" style="font-size: 10px; transition: transform 0.2s;"></i>
                    </button>
                    <div id="inline-actions-container" class="inline-actions-buttons">
                        <button type="button" id="detail-selesai-btn" class="btn btn-gold" style="display: inline-flex; align-items: center; gap: 4px; margin: 0; padding: 6px 12px; font-size: 12px; white-space: nowrap;">
                            <i class="fas fa-check-circle"></i> Selesaikan Konsultasi
                        </button>
                        <button type="button" id="detail-cancel-btn" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 4px; margin: 0; padding: 6px 12px; font-size: 12px; background-color: #ef4444; border: none; color: white; white-space: nowrap;">
                            <i class="fas fa-ban"></i> Batalkan Konsultasi
                        </button>
                    </div>
                </div>
            @endif
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-ticket')" style="margin: 0;">Tutup</button>
            <a href="#" id="detail-chat-btn" class="btn btn-primary" style="margin: 0; display: inline-flex; align-items: center; gap: 4px;"><i class="fas fa-comments"></i> Buka Pesan</a>
        </div>
    </div>
</div>

{{-- Modal Selesai & Buat Catatan --}}
@if(auth()->user()->isGuru())
<div class="modal-overlay" id="modal-selesai">
    <div class="modal" style="max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fa-solid fa-circle-check" style="color: #059669;"></i> Selesaikan & Buat Catatan
            </h3>
            <button class="modal-close" onclick="closeModal('modal-selesai')">✕</button>
        </div>
        <form id="form-selesai-konsultasi" method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <input type="hidden" name="ticket_id" id="selesai-ticket-id">
            <div class="field-group">
                <label>Topik Konsultasi</label>
                <input type="text" name="title" id="selesai-ticket-title" required>
            </div>
            <div class="field-group">
                <label>Masalah / Permasalahan</label>
                <textarea name="masalah" id="selesai-ticket-description" required></textarea>
            </div>
            <div class="field-group">
                <label>Tindakan yang Dilakukan (Solusi)</label>
                <textarea name="tindakan" placeholder="Tindakan, teknik, atau intervensi..." required></textarea>
            </div>
            <div class="field-group">
                <label>Kesimpulan (Opsional)</label>
                <textarea name="kesimpulan" placeholder="Kesimpulan sesi konseling..."></textarea>
            </div>
            <div class="modal-footer" style="justify-content: flex-end; gap: 10px; border-top: none; padding-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-selesai')">Batal</button>
                <button type="button" class="btn btn-primary" style="background: #059669; border: none; color: #fff;" onclick="openConfirmSelesai()"><i class="fa-solid fa-floppy-disk"></i> Simpan & Selesaikan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modal-confirm-selesai" style="z-index: 1060;">
    <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
        <div style="font-size: 3rem; color: #059669; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0 0 10px 0;">Selesaikan Konsultasi?</h3>
        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 24px 0;">
            Apakah Anda yakin ingin menyelesaikan sesi bimbingan ini dan menyimpan catatan konseling? Sesi chat akan ditutup secara permanen.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-selesai')" style="margin: 0; padding: 10px 20px;">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitSelesaiForm()" style="margin: 0; padding: 10px 20px; background: #059669; border: none; color: white;"><i class="fas fa-check-circle"></i> Ya, Selesaikan</button>
        </div>
    </div>
</div>
@endif

{{-- Step 1: Cancel reason form --}}
<div class="modal-overlay" id="modal-cancel-ticket">
    <div class="modal" style="max-width: 500px; text-align: left; padding: 24px;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fas fa-ban" style="color: #ef4444;"></i> Batalkan Konsultasi
            </h3>
            <button class="modal-close" onclick="closeModal('modal-cancel-ticket')">✕</button>
        </div>
        <form id="form-cancel-ticket" method="POST" action="">
            @csrf
            <input type="hidden" name="status" value="dibatalkan">
            <div class="field-group">
                <label style="font-weight: 700; font-size: 13px; color: var(--slate); display: block; margin-bottom: 6px;">Alasan Pembatalan</label>
                <textarea name="cancel_reason" placeholder="Jelaskan alasan pembatalan bimbingan/konsultasi ini..." required style="width: 100%; min-height: 100px; box-sizing: border-box;"></textarea>
            </div>
            <div class="modal-footer" style="justify-content: flex-end; gap: 10px; border-top: none; padding-top: 20px; display: flex;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-cancel-ticket')">Batal</button>
                <button type="button" class="btn btn-danger" onclick="openConfirmCancelSubmit()" style="background: #ef4444; border: none; color: #fff;"><i class="fas fa-ban"></i> Batalkan Konsultasi</button>
            </div>
        </form>
    </div>
</div>

{{-- Step 2: Confirm before submit --}}
<div class="modal-overlay" id="modal-confirm-cancel" style="z-index: 1060;">
    <div class="modal" style="max-width: 450px; text-align: center; padding: 24px;">
        <div style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--charcoal); margin: 0 0 10px 0;">Konfirmasi Pembatalan</h3>
        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 24px 0;">
            Apakah Anda yakin ingin membatalkan konsultasi ini? Tindakan ini tidak dapat dibatalkan.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-confirm-cancel')" style="margin: 0; padding: 10px 20px;">Kembali</button>
            <button type="button" class="btn btn-danger" onclick="submitCancelForm()" style="margin: 0; padding: 10px 20px; background: #ef4444; border: none; color: white;"><i class="fas fa-ban"></i> Ya, Batalkan</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
window.openSelesaiModal = function(id, title, description) {
    const idInput = document.getElementById('selesai-ticket-id');
    const titleInput = document.getElementById('selesai-ticket-title');
    const descInput = document.getElementById('selesai-ticket-description');
    if (idInput) idInput.value = id;
    if (titleInput) titleInput.value = title;
    if (descInput) descInput.value = description;
    openModal('modal-selesai');
};

window.openConfirmSelesai = function() {
    const tindakanVal = document.querySelector('#modal-selesai textarea[name=tindakan]');
    if (tindakanVal && !tindakanVal.value.trim()) {
        alert('Tindakan yang Dilakukan wajib diisi!');
        tindakanVal.focus();
        return;
    }
    openModal('modal-confirm-selesai');
};

window.submitSelesaiForm = function() {
    document.getElementById('form-selesai-konsultasi').submit();
};

function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')})});

function openDetailModal(btn) {
    const ticketId = btn.getAttribute('data-id');
    const status = btn.getAttribute('data-status');
    const code = btn.getAttribute('data-code');
    const title = btn.getAttribute('data-title');
    const description = btn.getAttribute('data-description');
    const student = btn.getAttribute('data-student');
    const className = btn.getAttribute('data-class');
    const service = btn.getAttribute('data-service');
    const serviceColor = btn.getAttribute('data-service-color');
    const serviceIcon = btn.getAttribute('data-service-icon');
    const priorAction = btn.getAttribute('data-prior-action');
    const isAnonymous = btn.getAttribute('data-anonymous') === '1';
    const isSiswa = {{ auth()->user()->isSiswa() ? 'true' : 'false' }};
    const teacher = btn.getAttribute('data-teacher');
    const cancelReason = btn.getAttribute('data-cancel-reason');
    const ticketDate = btn.getAttribute('data-date') || '-';

    document.getElementById('detail-code').innerText = code;
    document.getElementById('detail-title').innerText = title;
    document.getElementById('detail-description').innerText = description;
    document.getElementById('detail-teacher-info').innerText = teacher;
    document.getElementById('detail-date-info').innerText = ticketDate;
    
    if (isSiswa && isAnonymous) {
        document.getElementById('detail-student-info').innerText = 'Anonim';
        const classContainer = document.getElementById('detail-class-info').parentElement;
        if (classContainer) classContainer.style.display = 'none';
        const bannerStudent = document.getElementById('detail-anon-banner-student');
        if (bannerStudent) bannerStudent.style.display = 'flex';
    } else {
        document.getElementById('detail-student-info').innerText = student;
        const classContainer = document.getElementById('detail-class-info').parentElement;
        if (classContainer) {
            classContainer.style.display = 'block';
            document.getElementById('detail-class-info').innerText = className;
        }
        const bannerStudent = document.getElementById('detail-anon-banner-student');
        if (bannerStudent) bannerStudent.style.display = 'none';
        const bannerGuru = document.getElementById('detail-anon-banner-guru');
        if (bannerGuru) bannerGuru.style.display = isAnonymous ? 'flex' : 'none';
    }
    
    const badge = document.getElementById('detail-service-badge');
    if (badge) {
        badge.style.backgroundColor = serviceColor;
        badge.innerHTML = `<i class="${serviceIcon}"></i> ${service}`;
    }

    const cancelWrapper = document.getElementById('detail-cancel-reason-wrapper');
    const cancelEl = document.getElementById('detail-cancel-reason');
    if (status === 'dibatalkan' && cancelReason && cancelReason.trim().length > 0) {
        if (cancelEl) cancelEl.innerText = cancelReason;
        if (cancelWrapper) cancelWrapper.style.display = 'block';
    } else {
        if (cancelWrapper) cancelWrapper.style.display = 'none';
    }

    const chatBtn = document.getElementById('detail-chat-btn');
    if (chatBtn) {
        chatBtn.href = '/chat/' + ticketId;
        if (status === 'selesai' || status === 'dibatalkan') {
            chatBtn.className = 'btn btn-secondary';
            chatBtn.innerHTML = '<i class="fas fa-eye"></i> Lihat Pesan';
        } else {
            chatBtn.className = 'btn btn-primary';
            chatBtn.innerHTML = '<i class="fas fa-comments"></i> Balas Pesan';
        }
    }

    const actionsWrapper = document.getElementById('detail-actions-wrapper');
    if (actionsWrapper) {
        if (status === 'selesai' || status === 'dibatalkan') {
            actionsWrapper.style.display = 'none';
        } else {
            actionsWrapper.style.display = 'inline-flex';
            
            // Reset collapsible elements state
            const container = document.getElementById('inline-actions-container');
            if (container) container.style.display = 'none';
            const chevron = document.getElementById('actions-chevron');
            if (chevron) {
                chevron.style.transform = 'rotate(0deg)';
                chevron.className = 'fas fa-chevron-right';
            }

            const selesaiBtn = document.getElementById('detail-selesai-btn');
            if (selesaiBtn) {
                selesaiBtn.onclick = function(e) {
                    e.preventDefault();
                    closeModal('modal-detail-ticket');
                    openSelesaiModal(ticketId, title, description);
                };
            }

            const cancelBtn = document.getElementById('detail-cancel-btn');
            if (cancelBtn) {
                cancelBtn.onclick = function(e) {
                    e.preventDefault();
                    closeModal('modal-detail-ticket');
                    openConfirmCancelModal(ticketId);
                };
            }
        }
    }

    openModal('modal-detail-ticket');
}

window.toggleInlineActions = function() {
    const container = document.getElementById('inline-actions-container');
    const chevron = document.getElementById('actions-chevron');
    if (container) {
        if (container.style.display === 'none' || container.style.display === '') {
            container.style.display = 'inline-flex';
            if (chevron) {
                chevron.style.transform = 'rotate(90deg)';
            }
        } else {
            container.style.display = 'none';
            if (chevron) {
                chevron.style.transform = 'rotate(0deg)';
            }
        }
    }
};

// Open cancel reason form first
window.openConfirmCancelModal = function(id) {
    window._pendingCancelId = id;
    const form = document.getElementById('form-cancel-ticket');
    if (form) {
        form.action = '/tickets/' + id + '/status';
        const ta = form.querySelector('textarea[name=cancel_reason]');
        if (ta) ta.value = '';
    }
    openModal('modal-cancel-ticket');
};

// Alias
window.openCancelModal = window.openConfirmCancelModal;

// After filling reason, show confirm modal
window.openConfirmCancelSubmit = function() {
    const ta = document.querySelector('#form-cancel-ticket textarea[name=cancel_reason]');
    if (!ta || !ta.value.trim()) {
        ta && ta.focus();
        return;
    }
    openModal('modal-confirm-cancel');
};

// Final submit after confirmation
window.submitCancelForm = function() {
    closeModal('modal-confirm-cancel');
    document.getElementById('form-cancel-ticket').submit();
};

// Real-time search & filter script via AJAX (solves glitch/race condition on hold backspace)
const filterForm = document.querySelector('.filter-bar');
const searchInput = document.getElementById('search-input');
const ticketsContainer = document.getElementById('tickets-container');
let activeSearchRequest = null;
let searchTimeout = null;

function toggleChip(name, btn) {
    const input = document.getElementById(`filter-${name}-input`);
    if (input) {
        if (input.value === '1') {
            input.value = '';
            btn.classList.remove('active');
        } else {
            input.value = '1';
            btn.classList.add('active');
        }
        fetchTickets();
    }
}

function fetchTicketsWithParams(params, isSearch = false) {
    if (activeSearchRequest) {
        activeSearchRequest.abort();
    }
    
    activeSearchRequest = new AbortController();
    const signal = activeSearchRequest.signal;
    
    fetch('{{ route("tickets.index") }}?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: signal
    })
    .then(res => {
        if (!res.ok) throw new Error('Failed to fetch tickets');
        return res.text();
    })
    .then(html => {
        if (ticketsContainer) {
            ticketsContainer.innerHTML = html;
        }
        
        // Re-subscribe Echo for newly loaded tickets
        document.querySelectorAll('.ticket-card[data-ticket-id]').forEach(el => {
            const id = el.getAttribute('data-ticket-id');
            if (typeof subscribeToTicketEcho === 'function') {
                subscribeToTicketEcho(id);
            }
        });
        
        // Update browser URL
        window.history.pushState({}, '', '{{ route("tickets.index") }}?' + params.toString());
        
        // Maintain search input focus
        if (isSearch && searchInput) {
            searchInput.focus();
        }
    })
    .catch(err => {
        if (err.name !== 'AbortError') {
            console.error('Error fetching tickets:', err);
        }
    });
}

function fetchTickets(isSearch = false) {
    if (!filterForm) return;
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);
    fetchTicketsWithParams(params, isSearch);
}

if (filterForm) {
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchTickets();
    });
    
    // Bind all selects to live AJAX reload, removing native page submit
    filterForm.querySelectorAll('select').forEach(select => {
        select.removeAttribute('onchange');
        select.addEventListener('change', () => fetchTickets());
    });
}

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchTickets(true);
        }, 300); // 300ms debounce
    });
    
    // Put cursor at the end on load
    if (searchInput.value.trim().length > 0) {
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
        searchInput.focus();
    }
}

// Intercept pagination clicks for AJAX loading
if (ticketsContainer) {
    ticketsContainer.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            try {
                const url = new URL(link.href);
                const page = url.searchParams.get('page');
                
                const params = new URLSearchParams(new FormData(filterForm));
                params.set('page', page);
                
                fetchTicketsWithParams(params);
            } catch (err) {
                console.error('Error parsing pagination link:', err);
            }
        }
    });
}

// Active local polling flag to avoid double polling with layout global poll
window._isLocalTicketsPollingActive = true;

const subscribedEchoChannels = new Set();

window.subscribeToTicketEcho = function(id) {
    if (typeof Echo !== 'undefined' && '{{ env("PUSHER_APP_KEY") }}' && !subscribedEchoChannels.has(id)) {
        subscribedEchoChannels.add(id);
        window.Echo.private(`ticket.${id}`)
            .listen('.message.sent', (e) => {
                console.log('Ticket updated via Pusher:', id);
                pollTicketUpdates();
            });
    }
};

window.pollTicketUpdates = function() {
    const renderedIds = [];
    document.querySelectorAll('.ticket-card[data-ticket-id]').forEach(el => {
        renderedIds.push(el.getAttribute('data-ticket-id'));
    });

    const filterFormElement = document.querySelector('.filter-bar');
    const params = filterFormElement ? new URLSearchParams(new FormData(filterFormElement)) : new URLSearchParams();
    renderedIds.forEach(id => params.append('rendered_ids[]', id));

    fetch('{{ route("tickets.unread_counts") }}?' + params.toString())
        .then(res => res.json())
        .then(data => {
            // Update sidebar unread badge
            if (data.total_unread !== undefined && window.updateSidebarUnreadBadge) {
                window.updateSidebarUnreadBadge(data.total_unread);
            }

            // Update existing tickets
            if (data.updates) {
                Object.keys(data.updates).forEach(id => {
                    const update = data.updates[id];
                    const card = document.querySelector(`.ticket-card[data-ticket-id="${id}"]`);
                    if (card) {
                        // 1. Update status classes on card wrapper
                        card.className = `ticket-card ${update.status} ticket-list-item ` + 
                                         (card.classList.contains('is-pinned') ? 'is-pinned' : '') + ' ' +
                                         (card.classList.contains('is-favorite') ? 'is-favorite' : '');

                        // 2. Update status badges inside the card
                        card.querySelectorAll('.ticket-status-badge').forEach(badge => {
                            badge.className = `badge badge-${update.status} ticket-status-badge`;
                            badge.textContent = update.status_label;
                        });

                        // 3. Update unread count badge
                        const balasBtn = card.querySelector('.btn-balas');
                        if (balasBtn) {
                            let unreadBadge = balasBtn.querySelector('.unread-badge');
                            if (update.unread_count > 0) {
                                if (!unreadBadge) {
                                    unreadBadge = document.createElement('span');
                                    unreadBadge.className = 'unread-badge';
                                    unreadBadge.setAttribute('style', 'position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid white; line-height: 1;');
                                    balasBtn.appendChild(unreadBadge);
                                }
                                unreadBadge.textContent = update.unread_count > 9 ? '9+' : update.unread_count;
                            } else if (unreadBadge) {
                                unreadBadge.remove();
                            }
                        }

                        // 4. Update detail button attributes
                        const detailBtn = card.querySelector('.btn-detail-ticket');
                        if (detailBtn) {
                            detailBtn.setAttribute('data-status', update.status);
                        }

                        // 5. Update actions visibility (Selesaikan, Batalkan, Balas/Lihat)
                        if (update.status === 'selesai' || update.status === 'dibatalkan') {
                            // Hide the entire actions dropdown
                            const dropdown = card.querySelector('.ticket-actions-dropdown');
                            if (dropdown) dropdown.remove();

                            // Change Balas to Lihat
                            const balasBtn = card.querySelector('.btn-balas');
                            if (balasBtn) {
                                balasBtn.className = 'btn btn-secondary btn-sm';
                                balasBtn.innerHTML = '<i class="fas fa-eye"></i> Lihat';
                                balasBtn.removeAttribute('data-ticket-id');
                                // Remove unread count badge just in case
                                const unreadBadge = balasBtn.querySelector('.unread-badge');
                                if (unreadBadge) unreadBadge.remove();
                            }
                        }
                    }
                });
            }

            // Insert new tickets
            if (data.new_tickets && data.new_tickets.length > 0) {
                const listContainer = document.querySelector('.ticket-list');
                if (listContainer) {
                    const emptyState = listContainer.querySelector('.empty-state');
                    if (emptyState) emptyState.remove();

                    // Prepend new tickets (loop backward to maintain order)
                    for (let i = data.new_tickets.length - 1; i >= 0; i--) {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.new_tickets[i].trim();
                        const newCard = tempDiv.firstChild;
                        listContainer.insertBefore(newCard, listContainer.firstChild);

                        // If Echo is active, subscribe new card to private channel
                        if (newCard.getAttribute('data-ticket-id')) {
                            const newId = newCard.getAttribute('data-ticket-id');
                            window.subscribeToTicketEcho(newId);
                        }
                    }
                }
            }
        })
        .catch(err => console.error('Error polling ticket updates:', err));
};

// Initial subscriber setup
document.querySelectorAll('.ticket-card[data-ticket-id]').forEach(el => {
    const id = el.getAttribute('data-ticket-id');
    window.subscribeToTicketEcho(id);
});

// Setup 3s interval polling
setInterval(window.pollTicketUpdates, 3000);
</script>
@endpush
