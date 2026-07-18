@extends('layouts.app')
@section('title', 'Layanan Konsultasi')
@section('page-title', 'Layanan Konsultasi')

@push('styles')
<style>
    /* Lock the main page layout to fit viewport, preventing double scrollbars while enabling pull-to-refresh */
    html, body {
        height: 100% !important;
        overflow-y: auto !important; /* Enables pull-to-refresh on mobile */
        overflow-x: hidden !important;
    }
    .main-content {
        height: calc(100% - 64px) !important;
        overflow: hidden !important; /* Prevents outer layout scrolling */
        box-sizing: border-box;
    }
    .chat-card-container {
        height: 100% !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
</style>
@endpush

@section('content')

<div class="card chat-card-container">
    @if($active)

    {{-- 0. Chat Header Info (Compact & Clickable) --}}
    <div class="chat-header-info" onclick="openModal('modal-chat-detail')" style="cursor: pointer; padding: 12px 20px; border-bottom: 1px solid #eee; transition: background 0.2s; flex-shrink: 0; background: #fff;" onmouseover="this.style.background='#fafdfc'" onmouseout="this.style.background='transparent'">
        <div style="flex-grow: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--charcoal);">
                        {{ $active->student->user->name ?? '-' }}
                    </h4>
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--teal); background: rgba(13,124,102,0.08); padding: 1px 6px; border-radius: 4px; display: inline-block;">
                        Kelas: {{ $active->student->class->name ?? '-' }}
                    </span>
                    @if($active->service)
                        <span class="badge" style="background: {{ $active->service->color ?? 'var(--teal)' }}; color: #fff; font-size: 9px; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                            <i class="{{ $active->service->icon ?? 'fas fa-tag' }}"></i> {{ $active->service->name }}
                        </span>
                    @endif
                </div>
                <div style="color: var(--muted); font-size: 12px; display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                    <span style="font-size: 11px; font-weight: 500;">Detail</span>
                    <i class="fa-solid fa-circle-info"></i>
                </div>
            </div>
            @if($active->anonymous)
                <div style="display: flex; align-items: center; gap: 6px; color: #dc2626; font-size: 11px; font-weight: 600; margin-top: 2px;">
                    <i class="fas fa-user-secret" style="font-size: 12px; animation: pulse 1.5s infinite; flex-shrink: 0;"></i>
                    <span><strong>Sesi Konsultasi Anonim:</strong> Siswa mengajukan konsultasi ini secara anonim untuk menjaga kerahasiaan identitas aslinya.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- 1. Chat Messages Area --}}
    <div class="chat-messages" id="chat-messages" style="flex-grow: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; background: #f8f9fa;">
        @foreach ($messages as $msg)
        <div class="msg {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}" data-id="{{ $msg->id }}">
            <div class="msg-avatar" @if($msg->sender_id === auth()->id()) style="background:var(--teal-dark)" @endif>{{ $msg->sender->avatar_initials }}</div>
            <div class="msg-body">
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
    .msg-body {
        display: flex;
        flex-direction: column;
        max-width: 75%;
    }
    .sent .msg-body {
        align-items: flex-end;
    }
    .received .msg-body {
        align-items: flex-start;
    }
    .msg-bubble {
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 0.95rem;
        word-wrap: break-word;
        max-width: 100%;
        width: fit-content;
    }
    .sent .msg-bubble { background: var(--teal); color: white; border-bottom-right-radius: 2px; }
    .received .msg-bubble { background: #eee; color: #333; border-bottom-left-radius: 2px; }
    .msg { display: flex; gap: 10px; }
    .msg.sent { flex-direction: row-reverse; }
</style>

<script>
{
    const msgInput = document.getElementById('msg-input');
    const btnRecord = document.getElementById('btn-record');
    const btnSend = document.getElementById('btn-send');
    const btnLoading = document.getElementById('btn-loading');
    const chatForm = document.getElementById('chat-form');
    const chatBox = document.getElementById('chat-messages');

    // 1. Logika Toggle Mic vs Send
    if (msgInput) {
        msgInput.addEventListener('input', function() {
            // Auto resize height
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';

            if (this.value.trim().length > 0) {
                if (btnRecord) btnRecord.style.display = 'none';
                if (btnSend) btnSend.style.display = 'block';
            } else {
                if (btnRecord) btnRecord.style.display = 'block';
                if (btnSend) btnSend.style.display = 'none';
            }
        });

        // Enter to send, Shift+Enter to newline
        msgInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim().length > 0 && chatForm) {
                    if (typeof chatForm.requestSubmit === 'function') {
                        chatForm.requestSubmit();
                    } else {
                        chatForm.submit();
                    }
                }
            }
        });
    }

    // 2. AJAX Submit (Pesan langsung terkirim tanpa refresh halaman)
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Capture FormData before disabling input elements to avoid null/ignored values
            const formData = new FormData(this);

            if (btnSend) btnSend.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'block';
            if (msgInput) msgInput.disabled = true;

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to send message');
                return res.json();
            })
            .then(data => {
                if (msgInput) {
                    msgInput.value = '';
                    msgInput.disabled = false;
                    msgInput.style.height = 'auto';
                    msgInput.focus();
                }
                const fileInput = document.getElementById('file-input');
                if (fileInput) fileInput.value = '';

                if (btnSend) btnSend.style.display = 'none';
                if (btnRecord) btnRecord.style.display = 'block';
                if (btnLoading) btnLoading.style.display = 'none';

                // Langsung ambil pesan baru
                if (typeof window.fetchNewMessages === 'function') {
                    window.fetchNewMessages();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal mengirim pesan. Silakan coba lagi.');
                if (msgInput) msgInput.disabled = false;
                if (btnLoading) btnLoading.style.display = 'none';
                if (msgInput && msgInput.value.trim().length > 0) {
                    if (btnSend) btnSend.style.display = 'block';
                } else {
                    if (btnRecord) btnRecord.style.display = 'block';
                }
            });
        });
    }

    // 3. Handle File (Upload instan via AJAX)
    window.handleFileSelect = function() {
        if (!chatForm) return;
        
        // Capture FormData before disabling input elements to avoid null/ignored values
        const formData = new FormData(chatForm);

        if (btnRecord) btnRecord.style.display = 'none';
        if (btnSend) btnSend.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'block';
        if (msgInput) msgInput.disabled = true;

        fetch(chatForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to upload file');
            return res.json();
        })
        .then(data => {
            if (msgInput) {
                msgInput.value = '';
                msgInput.disabled = false;
                msgInput.style.height = 'auto';
            }
            const fileInput = document.getElementById('file-input');
            if (fileInput) fileInput.value = '';

            if (btnSend) btnSend.style.display = 'none';
            if (btnRecord) btnRecord.style.display = 'block';
            if (btnLoading) btnLoading.style.display = 'none';

            if (typeof window.fetchNewMessages === 'function') {
                window.fetchNewMessages();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal mengirim file. Pastikan ukuran file tidak melebihi 10MB.');
            if (msgInput) msgInput.disabled = false;
            if (btnLoading) btnLoading.style.display = 'none';
            if (btnRecord) btnRecord.style.display = 'block';
        });
    }

    // 4. Scroll ke bawah
    if(chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}
</script>


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



@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
{
    // Auto-scroll chat
    const msgs = document.getElementById('chat-messages');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;

    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    };
    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    };

    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', e => {
            if (e.target === m) m.classList.remove('open');
        });
    });

    // Real-Time Chat & Fallback Polling Integration
    @if($active)
        // Track message IDs currently rendered on screen
        let renderedMessageIds = new Set();
        document.querySelectorAll('#chat-messages .msg').forEach(el => {
            const id = el.getAttribute('data-id');
            if (id) renderedMessageIds.add(parseInt(id));
        });

        // Function to fetch messages dynamically
        window.fetchNewMessages = function() {
            fetch('{{ route("chat.messages", $active) }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(messages => {
                let hasNew = false;
                const msgsDiv = document.getElementById('chat-messages');
                
                messages.forEach(msg => {
                    if (!renderedMessageIds.has(msg.id)) {
                        renderedMessageIds.add(msg.id);
                        hasNew = true;
                        
                        const msgEl = document.createElement('div');
                        msgEl.className = `msg ${msg.is_me ? 'sent' : 'received'}`;
                        msgEl.setAttribute('data-id', msg.id);
                        
                        let contentHtml = '';
                        if(msg.type === 'text') {
                            contentHtml = `<div class="msg-bubble">${msg.content}</div>`;
                        } else if(msg.type === 'image') {
                            contentHtml = `<div class="msg-bubble"><img src="${msg.file_url}" class="msg-img" alt="Image" style="max-width: 100%; border-radius: 8px; margin-bottom: 5px;"><br>${msg.content || ''}</div>`;
                        } else if(msg.type === 'audio') {
                            contentHtml = `<div class="msg-bubble" style="padding:8px"><audio controls src="${msg.file_url}" style="height:36px;max-width:220px"></audio></div>`;
                        } else if(msg.type === 'video') {
                            contentHtml = `<div class="msg-bubble" style="padding:8px"><video controls src="${msg.file_url}" style="max-width:220px; border-radius: var(--radius-sm);"></video></div>`;
                        } else {
                            contentHtml = `<div class="msg-bubble"><a href="${msg.file_url}" target="_blank" style="color:inherit; text-decoration: none;"><i class="fa-solid fa-file"></i> ${msg.file_name}</a></div>`;
                        }
                        
                        msgEl.innerHTML = `
                            <div class="msg-avatar" ${msg.is_me ? 'style="background:var(--teal-dark)"' : ''}>${msg.sender.initials}</div>
                            <div class="msg-body">
                                ${contentHtml}
                                <div class="msg-time" style="font-size: 0.7rem; color: #999; margin-top: 4px; text-align: ${msg.is_me ? 'right' : 'left'};">${msg.time}</div>
                            </div>
                        `;
                        
                        if (msgsDiv) {
                            msgsDiv.appendChild(msgEl);
                        }
                    }
                });
                
                if (hasNew && msgsDiv) {
                    msgsDiv.scrollTop = msgsDiv.scrollHeight;
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
        };

        // Fallback polling: fetch every 3 seconds to ensure 100% real-time compatibility on cPanel
        const pollInterval = setInterval(window.fetchNewMessages, 3000);

        // Echo setup (Pusher)
        if (typeof Echo !== 'undefined' && '{{ env("PUSHER_APP_KEY") }}') {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env("PUSHER_APP_KEY") }}',
                cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
                forceTLS: true
            });

            window.Echo.private(`ticket.{{ $active->id }}`)
                .listen('.message.sent', (e) => {
                    console.log('New Message via Pusher:', e);
                    window.fetchNewMessages();
                });
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
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            }).then(res => {
                                if(res.ok) {
                                    document.getElementById('msg-input').placeholder = 'Ketik pesan...';
                                    document.getElementById('msg-input').disabled = false;
                                    window.fetchNewMessages();
                                } else {
                                    alert('Gagal mengirim Voice Note');
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
}
</script>
@endpush

@push('modals')
<div class="modal-overlay" id="modal-chat-detail">
    <div class="modal" style="max-width: 520px; padding: 24px; text-align: left;">
        <div class="modal-header" style="border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--charcoal); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fas fa-info-circle" style="color: var(--teal);"></i> Detail Konsultasi
            </h3>
            <button class="modal-close" onclick="closeModal('modal-chat-detail')">✕</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Siswa</label>
                    <div style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">
                        {{ $active->student->user->name ?? '-' }}
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Kelas</label>
                    <div style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">
                        {{ $active->student->class->name ?? '-' }}
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Layanan</label>
                    <div>
                        @if($active->service)
                            <span class="badge" style="background: {{ $active->service->color ?? 'var(--teal)' }}; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;">
                                <i class="{{ $active->service->icon ?? 'fas fa-tag' }}"></i> {{ $active->service->name }}
                            </span>
                        @else
                            <span style="font-size: 0.9rem; font-weight: 700; color: #1e293b;">-</span>
                        @endif
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 2px;">Kode Tiket</label>
                    <div>
                        <span style="font-weight: 800; color: var(--teal); font-size: 0.9rem;">{{ $active->code }}</span>
                    </div>
                </div>
            </div>
            
            @if($active->anonymous)
                <div style="display: flex; align-items: center; gap: 8px; background-color: #fef2f2; border: 1px solid #fee2e2; color: #dc2626; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                    <i class="fas fa-user-secret" style="font-size: 14px;"></i>
                    <span><strong>Sesi Konsultasi Anonim:</strong> Siswa mengajukan konsultasi ini secara anonim untuk menjaga kerahasiaan identitas aslinya.</span>
                </div>
            @endif

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Judul Konsultasi</label>
                <div style="font-size: 1rem; font-weight: 800; color: #0f172a;">{{ $active->title }}</div>
            </div>

            <div>
                <label style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Deskripsi Masalah</label>
                <div style="font-size: 0.9rem; color: #334155; background: #f8fafc; border-left: 4px solid var(--teal); padding: 12px; border-radius: 0 8px 8px 0; line-height: 1.5; white-space: pre-wrap; word-break: break-word;">{{ $active->description }}</div>
            </div>
        </div>
        <div class="modal-footer" style="margin-top: 24px; padding-top: 12px; border-top: 1px solid #eee; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-chat-detail')">Tutup</button>
        </div>
    </div>
</div>
@endpush