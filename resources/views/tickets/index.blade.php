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
    <input type="text" name="search" id="search-input" placeholder="Cari tiket, nama siswa..." value="{{ request('search') }}" autocomplete="off">
    <select name="status" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="menunggu" {{ request('status')=='menunggu'?'selected':'' }}>Menunggu</option>
        <option value="diproses" {{ request('status')=='diproses'?'selected':'' }}>Diproses</option>
        <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
    </select>
    <select name="service" onchange="this.form.submit()">
        <option value="">Semua Layanan</option>
        @foreach($services as $s)
            <option value="{{ $s->id }}" {{ request('service')==$s->id?'selected':'' }}>{{ $s->name }}</option>
        @endforeach
    </select>
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
    .ticket-list-item.is-favorite {
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
    @media (max-width: 768px) {
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
</style>

<!-- Ticket List -->
<div class="ticket-list" style="display: flex; flex-direction: column; gap: 12px;">
    @forelse ($tickets as $ticket)
    <div class="ticket-card {{ $ticket->status }} ticket-list-item {{ $ticket->is_favorite ? 'is-favorite' : '' }}" style="position: relative; padding-top: 24px;">
        
        {{-- Absolute Ticket Code & Date at Top Right --}}
        <span class="ticket-id" style="position: absolute; top: 8px; right: 20px; font-weight: 700; font-size: 11px; margin: 0; color: var(--slate); opacity: 0.7;">{{ $ticket->code }} <span style="font-weight: 500; margin-left: 6px; color: var(--muted);">({{ $ticket->created_at->format('d M Y - H:i') }})</span></span>

        {{-- Leftmost Side: Student Info & Favorite Star --}}
        <div style="flex: 0 0 240px; min-width: 0; display: flex; align-items: center; gap: 10px;">
            {{-- Favorite Star Button --}}
            <form method="POST" action="{{ route('tickets.favorite', $ticket) }}" style="display: inline; flex-shrink: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; cursor: pointer; color: {{ $ticket->is_favorite ? '#f59e0b' : '#d1d5db' }}; font-size: 1.15rem; padding: 2px; line-height: 1;" title="{{ $ticket->is_favorite ? 'Batal Favorit' : 'Jadikan Favorit' }}">
                    <i class="fa-{{ $ticket->is_favorite ? 'solid' : 'regular' }} fa-star"></i>
                </button>
            </form>
            
            {{-- Avatar & Name/Class & Status Badge --}}
            @if($ticket->student)
                <div class="ticket-guru-avatar" style="flex-shrink: 0; margin: 0;">{{ $ticket->student->avatar_initials }}</div>
                <div style="min-width: 0; display: flex; flex-direction: column; gap: 3px;">
                    <div style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1;">{{ $ticket->student->class->name ?? '-' }}</div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--navy); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;" title="{{ $ticket->student->user->name ?? '-' }}">{{ $ticket->student->user->name ?? '-' }}</div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <span class="badge badge-{{ $ticket->status }}" style="margin: 2px 0 0 0; width: fit-content; padding: 2px 8px; font-size: 10px; border-radius: 4px; line-height: 1.2;">{{ $ticket->status_label }}</span>
                        @if($ticket->anonymous)
                            <span class="badge" style="background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; margin: 2px 0 0 0; width: fit-content; padding: 2px 8px; font-size: 10px; border-radius: 4px; line-height: 1.2; font-weight: 600;" title="Pengajuan sebagai Anonim">
                                <i class="fas fa-user-secret"></i> Anonim
                            </span>
                        @endif
                    </div>
                </div>
            @else
                <span class="text-muted" style="font-size:12px;">Siswa tidak ditemukan</span>
            @endif
        </div>

        {{-- Middle: Badges, Title & Desc --}}
        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px;">
            {{-- Badges row --}}
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                @if($ticket->service)
                    <span class="badge" style="background: {{ $ticket->service->color ?? '#3d5454' }}; color: #fff; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; font-size: 10px; padding: 2px 8px; border-radius: 4px; margin: 0; width: fit-content;">
                        <i class="{{ $ticket->service->icon ?? 'fas fa-tag' }}"></i> {{ $ticket->service->name }}
                    </span>
                @endif
            </div>
            
            {{-- Title & Description --}}
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <div class="ticket-title" style="margin: 0; font-size: 15px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 6px;">
                    @if($ticket->is_favorite)
                        <span style="font-size: 10px; background: #fef3c7; color: #d97706; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Favorit</span>
                    @endif
                    {{ $ticket->title }}
                </div>
                <div class="ticket-desc" style="margin: 0; font-size: 13px; color: var(--slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ticket->description }}</div>
            </div>
        </div>

        {{-- Right Side: Actions --}}
        <div style="flex: 0 0 250px; display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
            @if(auth()->user()->isGuru() && $ticket->status !== 'selesai')
                <button type="button" class="btn btn-success btn-sm" style="padding: 6px 12px; font-size: 12px; margin: 0; background: #4f46e5; border: none; color: #fff; display: inline-flex; align-items: center; gap: 4px;" onclick="openSelesaiModal('{{ $ticket->id }}', '{{ addslashes($ticket->title) }}', '{{ addslashes($ticket->description) }}')">
                    <i class="fas fa-check-circle"></i> Selesai
                </button>
            @endif
            <button type="button" 
                    class="btn btn-secondary btn-sm" 
                    title="Detail Masalah" 
                    onclick="openDetailModal(this)"
                    data-id="{{ $ticket->id }}"
                    data-status="{{ $ticket->status }}"
                    data-code="{{ $ticket->code }}"
                    data-title="{{ $ticket->title }}"
                    data-description="{{ $ticket->description }}"
                    data-student="{{ $ticket->student?->user?->name ?? 'Anonim' }}"
                    data-class="{{ $ticket->student?->class?->name ?? '-' }}"
                    data-service="{{ $ticket->service?->name ?? '-' }}"
                    data-service-color="{{ $ticket->service?->color ?? '#3d5454' }}"
                    data-service-icon="{{ $ticket->service?->icon ?? 'fas fa-tag' }}"
                    data-prior-action="{{ $ticket->prior_action ?? '' }}"
                    style="padding: 6px 12px; font-size: 12px; margin: 0;">
                <i class="fas fa-info-circle"></i> Detail
            </button>
            @if($ticket->status !== 'selesai')
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 12px; margin: 0;"><i class="fas fa-reply"></i> {{ $ticket->status==='menunggu'?'Balas':'Lanjut' }}</a>
            @else
                <a href="{{ route('chat.show', $ticket) }}" class="btn btn-secondary btn-sm" style="padding: 6px 12px; font-size: 12px; margin: 0;"><i class="fas fa-eye"></i> Lihat</a>
            @endif
        </div>

    </div>
    @empty
    <div class="empty-state" style="width: 100%;">
        <i class="fas fa-ticket-alt"></i>
        <p>Belum ada tiket konsultasi</p>
        @if(auth()->user()->isSiswa())
            <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-20"><i class="fas fa-plus"></i> Ajukan Konsultasi</a>
        @endif
    </div>
    @endforelse
</div>
{{ $tickets->links('vendor.pagination.custom') }}
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
            <div class="field-group"><label>Judul</label><input type="text" name="title" placeholder="Judul tiket" required></div>
            <div class="field-group"><label>Deskripsi Masalah</label><textarea name="description" placeholder="Jelaskan masalah..." required></textarea></div>
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
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kategori Layanan</label>
                    <span id="detail-service-badge" class="badge" style="color: #fff; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 6px;"></span>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px;">Kode Tiket</label>
                    <span id="detail-code" style="font-weight: 800; color: var(--teal); font-size: 0.95rem;"></span>
                </div>
            </div>

            <!-- Detail Masalah -->
            <div style="text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Judul Konsultasi</label>
                <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a;" id="detail-title"></div>
            </div>

            <div style="text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Deskripsi Masalah</label>
                <div style="font-size: 0.925rem; color: #334155; background: #f8fafc; border-left: 4px solid var(--teal); padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;" id="detail-description"></div>
            </div>

            <div id="detail-prior-action-wrapper" style="display: none; text-align: left;">
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">Tindakan yang Pernah Dilakukan Sebelumnya</label>
                <div style="font-size: 0.925rem; color: #334155; background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 0 8px 8px 0; line-height: 1.6; white-space: pre-wrap;" id="detail-prior-action"></div>
            </div>
        </div>
        <div class="modal-footer" style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div>
                @if(auth()->user()->isGuru())
                    <button type="button" id="detail-selesai-btn" class="btn" style="background: #4f46e5; border: none; color: #fff; display: inline-flex; align-items: center; gap: 4px; margin: 0;"><i class="fas fa-check-circle"></i> Selesai</button>
                @endif
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-detail-ticket')">Tutup</button>
                <a href="#" id="detail-chat-btn" class="btn btn-primary"><i class="fas fa-comments"></i> Buka Pesan</a>
            </div>
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
        <form method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <input type="hidden" name="ticket_id" id="selesai-ticket-id">
            <div class="field-group">
                <label>Judul / Topik</label>
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
                <button type="submit" class="btn btn-primary" style="background: #059669; border: none; color: #fff;"><i class="fa-solid fa-floppy-disk"></i> Simpan & Selesaikan</button>
            </div>
        </form>
    </div>
</div>
@endif
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

    document.getElementById('detail-code').innerText = code;
    document.getElementById('detail-title').innerText = title;
    document.getElementById('detail-student-info').innerText = student;
    document.getElementById('detail-class-info').innerText = className;
    document.getElementById('detail-description').innerText = description;
    
    const badge = document.getElementById('detail-service-badge');
    if (badge) {
        badge.style.backgroundColor = serviceColor;
        badge.innerHTML = `<i class="${serviceIcon}"></i> ${service}`;
    }

    const priorWrapper = document.getElementById('detail-prior-action-wrapper');
    const priorEl = document.getElementById('detail-prior-action');
    if (priorAction && priorAction.trim().length > 0) {
        priorEl.innerText = priorAction;
        priorWrapper.style.display = 'block';
    } else {
        priorWrapper.style.display = 'none';
    }

    const chatBtn = document.getElementById('detail-chat-btn');
    if (chatBtn) {
        chatBtn.href = '/chat/' + ticketId;
        if (status === 'selesai') {
            chatBtn.className = 'btn btn-secondary';
            chatBtn.innerHTML = '<i class="fas fa-eye"></i> Lihat Pesan';
        } else {
            chatBtn.className = 'btn btn-primary';
            chatBtn.innerHTML = '<i class="fas fa-comments"></i> Balas / Lanjut Pesan';
        }
    }

    const selesaiBtn = document.getElementById('detail-selesai-btn');
    if (selesaiBtn) {
        if (status === 'selesai') {
            selesaiBtn.style.display = 'none';
        } else {
            selesaiBtn.style.display = 'inline-flex';
            selesaiBtn.onclick = function() {
                closeModal('modal-detail-ticket');
                openSelesaiModal(ticketId, title, description);
            };
        }
    }

    openModal('modal-detail-ticket');
}

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
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.form.submit();
        }, 400); // 400ms debounce
    });
}
</script>
@endpush
