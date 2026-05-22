@extends('layouts.app')
@section('title', 'Layanan Konsultasi')
@section('page-title', 'Layanan Konsultasi')

@section('content')
<div class="page-header">
    <h2>Layanan Konsultasi</h2>
    <p>Chat real-time dengan {{ auth()->user()->isSiswa() ? 'Guru BK' : 'siswa' }}</p>
</div>

<div class="chat-layout">
    {{-- Chat List (left panel) --}}
    <div class="chat-list">
        <div class="chat-list-header">
            <i class="fas fa-inbox" style="color:var(--teal);margin-right:8px"></i>
            {{ auth()->user()->isSiswa() ? 'Tiket Saya' : 'Daftar Chat' }}
        </div>
        @forelse($tickets as $t)
        <a href="{{ route('chat.show', $t) }}" style="text-decoration:none">
            <div class="chat-item {{ isset($active) && $active->id === $t->id ? 'active' : '' }}">
                <div class="chat-item-avatar">
                    {{ auth()->user()->isSiswa() ? ($t->teacher?->avatar_initials ?? 'GB') : ($t->student?->avatar_initials ?? 'SS') }}
                </div>
                <div class="chat-item-info">
                    <h4>
                        @if(auth()->user()->isSiswa())
                            {{ $t->teacher?->user?->name ?? 'Guru BK' }}
                        @elseif($t->anonymous)
                            Anonim
                        @else
                            {{ $t->student?->user?->name ?? 'Siswa' }}
                        @endif
                    </h4>
                    <p>{{ $t->title }}</p>
                    <div style="margin-top:3px">
                        <span class="badge {{ $t->status_badge }}" style="font-size:10px">{{ $t->status_label }}</span>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:24px">
            <i class="fas fa-inbox" style="font-size:32px"></i>
            <p style="margin-top:8px">Belum ada percakapan</p>
        </div>
        @endforelse
    </div>

    {{-- Chat Area (right panel) --}}
    <div class="chat-area">
        @if(isset($active))
        <div class="chat-header">
            <div class="chat-item-avatar">
                {{ auth()->user()->isSiswa() ? ($active->teacher?->avatar_initials ?? 'GB') : ($active->student?->avatar_initials ?? 'SS') }}
            </div>
            <div>
                <h3>
                    @if(auth()->user()->isSiswa())
                        {{ $active->teacher?->user?->name ?? 'Guru BK' }}
                    @elseif($active->anonymous)
                        Siswa Anonim
                    @else
                        {{ $active->student?->user?->name ?? 'Siswa' }}
                    @endif
                </h3>
                <p>{{ $active->code }} · {{ $active->service?->name }} · <span class="badge {{ $active->status_badge }}" style="font-size:11px">{{ $active->status_label }}</span></p>
            </div>
            {{-- Guru actions --}}
            @if(auth()->user()->isGuru() && $active->status !== 'selesai')
            <div style="margin-left:auto;display:flex;gap:8px">
                <button class="btn btn-gold btn-sm" onclick="openModal('modal-selesai')">
                    <i class="fas fa-check-double"></i> Selesaikan
                </button>
                <button class="btn btn-secondary btn-sm" onclick="openModal('modal-catatan')">
                    <i class="fas fa-notes-medical"></i> Catat
                </button>
            </div>
            @endif
        </div>

        <div class="chat-messages" id="chat-messages">
            <div class="info-box" style="margin-bottom:8px">
                <i class="fas fa-shield-alt"></i>
                <span>Percakapan ini bersifat rahasia dan hanya dapat dilihat oleh pihak yang bersangkutan.</span>
            </div>
            @foreach($messages as $msg)
            @php $isMe = $msg->sender_id === auth()->id(); @endphp
            <div class="msg {{ $isMe ? 'sent' : 'received' }}">
                <div class="msg-avatar">{{ $msg->sender?->avatar_initials }}</div>
                <div>
                    @if($msg->type === 'image' && $msg->file_path)
                        <img src="{{ asset('storage/'.$msg->file_path) }}" class="msg-img" alt="{{ $msg->file_name }}">
                    @elseif(in_array($msg->type, ['audio','video']) && $msg->file_path)
                        <div class="msg-bubble">
                            <i class="fas {{ $msg->type === 'audio' ? 'fa-music' : 'fa-video' }}"></i>
                            <a href="{{ asset('storage/'.$msg->file_path) }}" target="_blank" style="color:inherit">{{ $msg->file_name }}</a>
                        </div>
                    @else
                        <div class="msg-bubble">{{ $msg->content }}</div>
                    @endif
                    <div class="msg-time">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Input area --}}
        @if($active->status !== 'selesai')
        <div class="chat-input-area">
            <input type="file" id="file-input" style="display:none" onchange="previewFile(this)">
            <button class="btn-attach" type="button" onclick="document.getElementById('file-input').click()" title="Lampirkan file">
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="text" id="msg-input" placeholder="{{ auth()->user()->isSiswa() ? 'Ketik pesanmu... (Rahasia)' : 'Ketik pesan...' }}" onkeydown="if(event.key==='Enter')sendMessage()">
            <button class="btn-send" onclick="sendMessage()" id="btn-send">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div id="file-preview" style="display:none;padding:8px 20px;border-top:1px solid var(--cream-dark);background:#fff">
            <span id="file-name" style="font-size:13px;color:var(--slate)"></span>
            <button onclick="clearFile()" style="background:none;border:none;color:var(--danger);cursor:pointer;margin-left:8px"><i class="fas fa-times"></i></button>
        </div>
        @else
        <div style="padding:16px 20px;text-align:center;color:var(--muted);font-size:13px;border-top:1px solid var(--cream-dark)">
            <i class="fas fa-lock"></i> Sesi konsultasi telah selesai
        </div>
        @endif

        @else
        {{-- No active chat --}}
        <div style="flex:1;display:flex;align-items:center;justify-content:center">
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <p>Pilih percakapan di sebelah kiri</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal Selesaikan (Guru) --}}
@if(isset($active) && auth()->user()->isGuru())
<div class="modal-overlay" id="modal-selesai">
    <div class="modal" style="max-width:400px">
        <div class="modal-header"><h3>Selesaikan Konsultasi</h3><button class="modal-close" onclick="closeModal('modal-selesai')">✕</button></div>
        <div class="info-box mb-20"><i class="fas fa-info-circle"></i><span>Setelah diselesaikan, sesi chat akan ditutup.</span></div>
        <form method="POST" action="{{ route('tickets.status', $active) }}">
            @csrf
            <input type="hidden" name="status" value="selesai">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-selesai')">Batal</button>
                <button type="submit" class="btn btn-gold"><i class="fas fa-check-double"></i> Tandai Selesai</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Catatan --}}
<div class="modal-overlay" id="modal-catatan">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Catatan Konseling</h3><button class="modal-close" onclick="closeModal('modal-catatan')">✕</button></div>
        <form method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $active->id }}">
            <div class="field-group"><label>Judul Catatan</label><input type="text" name="title" required placeholder="Judul catatan konseling"></div>
            <div class="field-group"><label>Masalah / Permasalahan</label><textarea name="masalah" required placeholder="Deskripsikan masalah..."></textarea></div>
            <div class="field-group"><label>Tindakan yang Dilakukan</label><textarea name="tindakan" required placeholder="Intervensi yang dilakukan..."></textarea></div>
            <div class="field-group"><label>Kesimpulan <span style="font-weight:400;color:var(--muted)">(opsional)</span></label><textarea name="kesimpulan" placeholder="Kesimpulan sesi..."></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-catatan')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const ticketId  = {{ isset($active) ? $active->id : 'null' }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let selectedFile = null;

async function sendMessage() {
    const input = document.getElementById('msg-input');
    const text  = input.value.trim();
    if (!text && !selectedFile) return;

    const form = new FormData();
    if (text) form.append('content', text);
    if (selectedFile) form.append('file', selectedFile);
    form.append('_token', csrfToken);

    input.value = '';
    clearFile();

    try {
        const res = await fetch(`/chat/${ticketId}/message`, { method:'POST', body: form, headers:{'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        appendMessage(data.message, true);
    } catch(e) { console.error(e); }
}

function appendMessage(msg, isMe) {
    const box = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.className = 'msg ' + (isMe ? 'sent' : 'received');
    let content = msg.type === 'image' && msg.file_path
        ? `<img src="/storage/${msg.file_path}" class="msg-img">`
        : `<div class="msg-bubble">${escapeHtml(msg.content || '')}</div>`;
    div.innerHTML = `
        <div class="msg-avatar">${msg.sender?.name?.substring(0,2).toUpperCase() || '??'}</div>
        <div>${content}<div class="msg-time">${new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</div></div>
    `;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

function previewFile(input) {
    if (input.files[0]) {
        selectedFile = input.files[0];
        document.getElementById('file-preview').style.display = 'flex';
        document.getElementById('file-name').textContent = selectedFile.name;
    }
}

function clearFile() {
    selectedFile = null;
    document.getElementById('file-input').value = '';
    document.getElementById('file-preview').style.display = 'none';
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Auto-scroll to bottom
const msgs = document.getElementById('chat-messages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

// Poll for new messages every 5s (fallback without Pusher)
@if(isset($active))
setInterval(async () => {
    try {
        const res = await fetch(`/chat/${ticketId}/messages`, { headers:{'X-Requested-With':'XMLHttpRequest'} });
        const data = await res.json();
        const box  = document.getElementById('chat-messages');
        const currentCount = box.querySelectorAll('.msg').length;
        if (data.length > currentCount) {
            // reload messages
            const newMsgs = data.slice(currentCount);
            newMsgs.forEach(m => appendMessage(m, m.is_me));
        }
    } catch(e) {}
}, 5000);
@endif
</script>
@endpush
