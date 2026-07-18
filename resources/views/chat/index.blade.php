@extends('layouts.app')
@section('title', 'Layanan Konsultasi')
@section('page-title', 'Layanan Konsultasi')

@section('content')

<div class="card" id="chat-container" style="padding: 0; overflow: hidden; background: #fff; height: calc(100vh - 140px); min-height: 400px; display: flex; flex-direction: column;">
    @if($active)

    {{-- 1. Chat Messages Area --}}
    <div class="chat-messages" id="chat-messages" style="flex-grow: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; background: #f8f9fa;">
        @foreach ($messages as $msg)
        <div class="msg {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
            <div class="msg-avatar" @if($msg->sender_id === auth()->id()) style="background:var(--teal-dark)" @endif>{{ $msg->sender->avatar_initials }}</div>
            <div>
                @if($msg->type === 'text')
                    <div class="msg-bubble">{!! nl2br(e($msg->content)) !!}</div>
                @elseif($msg->type === 'image')
                    <div class="msg-bubble">
                        <img src="{{ $msg->file_url }}" class="msg-img" style="max-width: 100%; border-radius: 8px; margin-bottom: 5px;" alt="Image">
                        @if($msg->content)<br>{!! nl2br(e($msg->content)) !!}@endif
                    </div>
                @elseif($msg->type === 'audio')
                    <div class="msg-bubble" style="padding:8px"><audio controls src="{{ $msg->file_url }}" style="height:36px;max-width:220px"></audio></div>
                @elseif($msg->type === 'video')
                    <div class="msg-bubble" style="padding:8px"><video controls src="{{ $msg->file_url }}" style="max-width:220px; border-radius: var(--radius-sm);"></video></div>
                @else
                    <div class="msg-bubble"><a href="{{ $msg->file_url }}" target="_blank" style="color:inherit; text-decoration: none;"><i class="fa-solid fa-file"></i> {{ $msg->file_name }}</a></div>
                @endif
                <div class="msg-time" style="font-size: 0.7rem; color: #999; margin-top: 4px; text-align: {{ $msg->sender_id === auth()->id() ? 'right' : 'left' }};">
                    {{ $msg->created_at->format('H:i') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 2. Bottom Input Area --}}
    <div style="flex-shrink: 0; background: #fff; border-top: 1px solid #eee; width: 100%;">
        @if($active->status !== 'selesai')
        <form method="POST" action="{{ route('chat.send', $active) }}" enctype="multipart/form-data" id="chat-form" style="margin: 0; padding: 12px 15px; display: flex; align-items: flex-end; gap: 8px;">
            @csrf
            
            {{-- Attachment Button --}}
            <label for="file-input" style="cursor: pointer; margin-bottom: 8px; padding: 5px; color: #666;">
                <i class="fa-solid fa-paperclip"></i>
            </label>
            <input type="file" name="file" id="file-input" style="display:none" onchange="handleFileSelect()">
            
            {{-- Input Textarea --}}
            <textarea name="content" placeholder="Ketik pesan..." id="msg-input" rows="1" 
                style="flex-grow: 1; resize: none; border: 1px solid #e0e0e0; border-radius: 20px; padding: 8px 15px; min-height: 40px; max-height: 120px; overflow-y: auto; line-height: 1.5; outline: none; transition: border 0.2s;"></textarea>
            
            {{-- Dynamic Action Button (Mic / Send / Loading) --}}
            <div id="action-wrapper" style="margin-bottom: 5px;">
                <button type="button" id="btn-record" class="btn-chat-action" style="color: var(--teal); border:none; background:none; font-size: 1.2rem; cursor:pointer;">
                    <i class="fa-solid fa-microphone"></i>
                </button>
                <button type="submit" id="btn-send" class="btn-chat-action" style="display: none; color: var(--teal); border:none; background:none; font-size: 1.2rem; cursor:pointer;">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
                <button type="button" id="btn-loading" class="btn-chat-action" style="display: none; color: #ccc; border:none; background:none; font-size: 1.2rem; cursor: not-allowed;" disabled>
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                </button>
            </div>
        </form>
        @else
        <div style="padding:15px; background: #fdfdfd; text-align:center; font-size: 0.9rem; color: #888;">
            <i class="fa-solid fa-lock"></i> Konsultasi telah selesai.
        </div>
        @endif
    </div>
    @endif
</div>

<style>
    /* Animasi sederhana untuk mic saat "aktif" (misal dipicu nanti) */
    .recording-active {
        color: #ff4d4d !important;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .msg-bubble {
        max-width: 85%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 0.95rem;
        word-wrap: break-word;
    }
    .sent .msg-bubble { background: var(--teal); color: white; border-bottom-right-radius: 2px; align-self: flex-end; }
    .received .msg-bubble { background: #eee; color: #333; border-bottom-left-radius: 2px; align-self: flex-start; }
    .msg { display: flex; gap: 10px; }
    .msg.sent { flex-direction: row-reverse; }
</style>

<script>
    const msgInput = document.getElementById('msg-input');
    const btnRecord = document.getElementById('btn-record');
    const btnSend = document.getElementById('btn-send');
    const btnLoading = document.getElementById('btn-loading');
    const chatForm = document.getElementById('chat-form');
    const chatBox = document.getElementById('chat-messages');

    // 1. Logika Toggle Mic vs Send
    msgInput.addEventListener('input', function() {
        // Auto resize height
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';

        if (this.value.trim().length > 0) {
            btnRecord.style.display = 'none';
            btnSend.style.display = 'block';
        } else {
            btnRecord.style.display = 'block';
            btnSend.style.display = 'none';
        }
    });

    // 2. Logika Submit (Cegah double input & ganti ke Loading)
    chatForm.addEventListener('submit', function(e) {
        btnSend.style.display = 'none';
        btnLoading.style.display = 'block';
        msgInput.readOnly = true; // Mencegah user mengetik saat kirim
    });

    // 3. Handle File (Langsung loading saat upload)
    function handleFileSelect() {
        btnRecord.style.display = 'none';
        btnSend.style.display = 'none';
        btnLoading.style.display = 'block';
        chatForm.submit();
    }

    // 4. Scroll ke bawah
    if(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // 5. Contoh pemicu animasi rekam (opsional jika fungsionalitas rekam siap)
    btnRecord.addEventListener('click', function() {
        this.classList.toggle('recording-active');
        // Tambahkan logika Web Audio API Anda di sini
    });
</script>
{{-- 3. Keterangan Bawah Kontainer (Kondisional berdasarkan Role User) --}}
<div style="text-align: center; padding: 12px; min-height: 44px; display: flex; align-items: center; justify-content: center;">
    @if($active && auth()->user()->isGuru() && $active->status !== 'selesai')
        {{-- Jika yang login adalah GURU BK, tampilkan Tombol Selesaikan Sesi --}}
        <button type="button" class="btn btn-primary" onclick="openModal('modal-selesai')" style="background: var(--teal); color: #fff; border: none; padding: 8px 20px; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: var(--shadow);">
            <i class="fa-solid fa-circle-check"></i> Selesaikan Konseling & Simpan Catatan
        </button>
    @else
        {{-- Jika yang login adalah SISWA, tampilkan Keterangan Kerahasiaan --}}
        <div style="color: var(--teal); font-size: 13px; font-weight: 500;">
            <i class="fa-solid fa-shield-halved"></i> Pesan ini bersifat rahasia dengan enkripsi end-to-end, hanya orang di obrolan yang bisa membaca atau membagikannya.
        </div>
    @endif
</div>

{{-- Script auto-scroll & penanganan textarea --}}
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const chatBox = document.getElementById('chat-messages');
        if(chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Trik agar textarea otomatis melar tinggi sesuai baris teks tanpa merusak layout
        const tx = document.getElementById('msg-input');
        if(tx) {
            tx.addEventListener("input", function() {
                this.style.height = "auto";
                this.style.height = (this.scrollHeight) + "px";
            });
        }
    });
</script>
@endpush
@endsection

@push('modals')
@if($active && auth()->user()->isGuru() && $active->status !== 'selesai')
<div class="modal-overlay" id="modal-selesai">
    <div class="modal">
        <div class="modal-header"><h3>Selesaikan & Buat Catatan</h3><button class="modal-close" onclick="closeModal('modal-selesai')">✕</button></div>
        <form method="POST" action="{{ route('catatan.store') }}">
            @csrf
            <input type="hidden" name="ticket_id" value="{{ $active->id }}">
            <div class="field-group">
                <label>Judul / Topik</label>
                <input type="text" name="title" value="{{ $active->title }}" required>
            </div>
            <div class="field-group">
                <label>Masalah / Permasalahan</label>
                <textarea name="masalah" required>{{ $active->description }}</textarea>
            </div>
            <div class="field-group">
                <label>Tindakan yang Dilakukan (Solusi)</label>
                <textarea name="tindakan" placeholder="Tindakan, teknik, atau intervensi..." required></textarea>
            </div>
            <div class="field-group">
                <label>Kesimpulan (Opsional)</label>
                <textarea name="kesimpulan" placeholder="Kesimpulan sesi konseling..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-selesai')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan & Selesaikan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
// Auto-scroll chat
const msgs = document.getElementById('chat-messages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;

// Enter to send (Hanya jika klik tombol pesawat hijau, enter di textarea murni ganti baris sekarang)
// Listener keydown submit dihilangkan agar teks textarea bisa berpindah baris dengan normal saat ditekan enter.

function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')})});

// Pusher Real-Time Chat Integration
@if($active)
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ env("PUSHER_APP_KEY") }}',
        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
        forceTLS: true
    });

    window.Echo.private(`ticket.{{ $active->id }}`)
        .listen('.message.sent', (e) => {
            console.log('New Message:', e);
            appendMessage(e);
        });

    function appendMessage(data) {
        const isMe = data.sender.id === {{ auth()->id() }};
        if(isMe) return;

        const msgsDiv = document.getElementById('chat-messages');
        let contentHtml = '';

        if(data.type === 'text') {
            contentHtml = `<div class="msg-bubble">${data.content}</div>`;
        } else if(data.type === 'image') {
            contentHtml = `<div class="msg-bubble"><img src="/storage/${data.file_path}" class="msg-img" alt="Image"><br>${data.content || ''}</div>`;
        } else if(data.type === 'audio') {
            contentHtml = `<div class="msg-bubble" style="padding:8px"><audio controls src="/storage/${data.file_path}" style="height:36px;max-width:220px"></audio></div>`;
        } else if(data.type === 'video') {
            contentHtml = `<div class="msg-bubble" style="padding:8px"><video controls src="/storage/${data.file_path}" style="max-width:220px; border-radius: var(--radius-sm);"></video></div>`;
        } else {
            contentHtml = `<div class="msg-bubble"><a href="/storage/${data.file_path}" target="_blank" style="color:inherit"><i class="fa-solid fa-file"></i> ${data.file_name}</a></div>`;
        }

        const msgEl = document.createElement('div');
        msgEl.className = 'msg received';
        msgEl.innerHTML = `
            <div class="msg-avatar">${data.sender.initials}</div>
            <div>
                ${contentHtml}
                <div class="msg-time">${data.time}</div>
            </div>
        `;

        msgsDiv.appendChild(msgEl);
        msgsDiv.scrollTop = msgsDiv.scrollHeight;
    }

    // Voice Note Recording
    let mediaRecorder;
    let audioChunks = [];
    const btnRecord = document.getElementById('btn-record');

    if(btnRecord) {
        btnRecord.addEventListener('click', async () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Akses mikrofon ditolak oleh browser. Perekaman suara (Voice Note) membutuhkan koneksi aman (HTTPS) atau localhost. Pastikan Anda mengakses situs ini menggunakan HTTPS (misal pada Herd: aktifkan ikon gembok "Secure").');
                return;
            }

            if (!mediaRecorder || mediaRecorder.state === 'inactive') {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];

                    mediaRecorder.ondataavailable = e => {
                        if (e.data.size > 0) audioChunks.push(e.data);
                    };

                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const formData = new FormData(document.getElementById('chat-form'));
                        formData.append('file', audioBlob, 'voicenote.webm');
                        
                        btnRecord.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                        btnRecord.style.color = 'var(--muted)';

                        fetch('{{ route("chat.send", $active) }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(res => {
                            if(res.ok) {
                                window.location.reload();
                            }
                        }).catch(err => {
                            console.error(err);
                            alert('Gagal mengirim Voice Note');
                        }).finally(() => {
                            btnRecord.innerHTML = '<i class="fa-solid fa-microphone"></i>';
                            btnRecord.style.color = 'var(--teal)';
                        });
                    };

                    mediaRecorder.start();
                    btnRecord.innerHTML = '<i class="fa-solid fa-stop"></i>';
                    btnRecord.style.color = 'var(--danger)';
                    document.getElementById('msg-input').placeholder = 'Merekam...';
                    document.getElementById('msg-input').disabled = true;

                } catch (err) {
                    alert('Gagal mengakses mikrofon: ' + err.message);
                }
            } else {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
                document.getElementById('msg-input').placeholder = 'Ketik pesan...';
                document.getElementById('msg-input').disabled = false;
            }
        });
    }
@endif
</script>
@endpush