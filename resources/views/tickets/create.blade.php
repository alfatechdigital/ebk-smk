@extends('layouts.app')
@section('title', 'Ajukan Konsultasi')
@section('page-title', 'Ajukan Konsultasi Baru')

@section('content')
<div class="page-header">
    <h2>Ajukan Konsultasi Baru</h2>
    <p>Ceritakan masalahmu kepada Guru BK terpercaya</p>
</div>

<div class="warning-box mb-20">
    <i class="fas fa-shield-alt"></i>
    <span>Konsultasi ini bersifat <b>sepenuhnya rahasia</b>. Guru BK berkomitmen menjaga privasi kamu.</span>
</div>

{{-- Step Indicator --}}
<div class="step-wizard" id="step-wizard">
    <div style="flex:1;text-align:center">
        <div class="step-dot active" id="dot-1">1</div>
        <p class="step-label active" id="lbl-1">Pilih Guru BK</p>
    </div>
    <div class="step-line"><div></div></div>
    <div style="flex:1;text-align:center">
        <div class="step-dot inactive" id="dot-2">2</div>
        <p class="step-label inactive" id="lbl-2">Jenis Layanan</p>
    </div>
    <div class="step-line"><div></div></div>
    <div style="flex:1;text-align:center">
        <div class="step-dot inactive" id="dot-3">3</div>
        <p class="step-label inactive" id="lbl-3">Detail Konsultasi</p>
    </div>
    <div class="step-line"><div></div></div>
    <div style="flex:1;text-align:center">
        <div class="step-dot inactive" id="dot-4">4</div>
        <p class="step-label inactive" id="lbl-4">Submit</p>
    </div>
</div>

<form method="POST" action="{{ route('tickets.store') }}" id="ticket-form">
@csrf

{{-- Step 1: Pilih Guru BK --}}
<div id="step-1">
    <div class="card">
        <div class="card-header"><div class="card-title">Pilih Guru BK yang ingin kamu konsultasikan</div></div>
        <div class="grid-2">
            @foreach($teachers as $i => $teacher)
            <div class="guru-opt {{ $i===0 ? 'selected' : '' }}" onclick="selectGuru(this, {{ $teacher->id }})"
                 style="border:2px solid {{ $i===0 ? 'var(--teal)' : 'var(--cream-dark)' }};border-radius:var(--radius);padding:20px;cursor:pointer;{{ $i===0 ? 'background:rgba(13,124,102,0.04)' : '' }}">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px">
                    <div style="width:56px;height:56px;border-radius:50%;background:var(--teal);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:700">{{ $teacher->avatar_initials }}</div>
                    <div><h4 style="font-weight:700;font-size:16px">{{ $teacher->user->name }}</h4>
                    <p class="text-muted">Spesialis: {{ $teacher->spesialisasi ?? 'Umum' }}</p></div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <span class="badge badge-success">Tersedia</span>
                    <span class="badge badge-info">{{ $teacher->tickets->count() }} sesi</span>
                </div>
            </div>
            @endforeach
        </div>
        <input type="hidden" name="teacher_id" id="selected-teacher" value="{{ $teachers->first()?->id }}">
        <div style="margin-top:20px;display:flex;justify-content:flex-end">
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Selanjutnya <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

{{-- Step 2: Jenis Layanan --}}
<div id="step-2" style="display:none">
    <div class="card">
        <div class="card-header"><div class="card-title">Pilih Jenis Layanan Konseling</div></div>
        <div class="grid-3" style="margin-bottom:20px">
            @foreach($services as $i => $svc)
            <div class="layanan-opt {{ $i===0 ? 'selected' : '' }}" onclick="selectLayanan(this, {{ $svc->id }})"
                 style="border:2px solid {{ $i===0 ? 'var(--teal)' : 'var(--cream-dark)' }};border-radius:var(--radius);padding:16px;cursor:pointer;text-align:center;{{ $i===0 ? 'background:rgba(13,124,102,0.04)' : '' }}">
                <i class="{{ $svc->icon }}" style="font-size:28px;color:{{ $svc->color }};margin-bottom:8px;display:block"></i>
                <p style="font-weight:700;font-size:14px">{{ $svc->name }}</p>
                <p class="text-muted" style="font-size:12px;margin-top:4px">{{ Str::limit($svc->description, 40) }}</p>
            </div>
            @endforeach
        </div>
        <input type="hidden" name="service_id" id="selected-service" value="{{ $services->first()?->id }}">
        <div style="display:flex;gap:10px;justify-content:space-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(1)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">Selanjutnya <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

{{-- Step 3: Detail --}}
<div id="step-3" style="display:none">
    <div class="card">
        <div class="card-header"><div class="card-title">Detail Konsultasi</div></div>
        <div class="field-group">
            <label>Judul / Topik Masalah</label>
            <input type="text" name="title" placeholder="Contoh: Masalah kepercayaan diri saat presentasi" required>
        </div>
        <div class="field-group">
            <label>Ceritakan masalahmu secara detail <span style="color:var(--teal)">(Rahasia)</span></label>
            <textarea name="description" style="min-height:150px" placeholder="Ceritakan apa yang sedang kamu alami..." required></textarea>
        </div>
        <div class="field-group">
            <label>Tindakan yang sudah kamu lakukan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <textarea name="prior_action" placeholder="Apakah kamu sudah mencoba melakukan sesuatu?"></textarea>
        </div>
        <div style="display:flex;gap:10px;justify-content:space-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(2)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(4)">Review &amp; Submit <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

{{-- Step 4: Review --}}
<div id="step-4" style="display:none">
    <div class="card">
        <div class="card-header"><div class="card-title">Review &amp; Konfirmasi</div></div>
        <div style="background:var(--cream);border-radius:var(--radius-sm);padding:20px;margin-bottom:20px">
            <div class="profile-info-grid">
                <div class="profile-info-item"><label>Guru BK</label><p id="review-guru">—</p></div>
                <div class="profile-info-item"><label>Jenis Layanan</label><p id="review-layanan">—</p></div>
                <div class="profile-info-item" style="grid-column:1/-1"><label>Topik Masalah</label><p id="review-title">—</p></div>
            </div>
        </div>
        <div class="info-box mb-20"><i class="fas fa-lock"></i><span>Konsultasi ini akan dirahasiakan. Hanya kamu dan Guru BK yang dapat mengaksesnya.</span></div>
        <div style="display:flex;gap:10px;justify-content:space-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(3)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Konsultasi</button>
        </div>
    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
let selectedGuruName = '{{ $teachers->first()?->user->name ?? '' }}';
let selectedLayananName = '{{ $services->first()?->name ?? '' }}';

function nextStep(n) {
    for (let i = 1; i <= 4; i++) {
        const s = document.getElementById('step-' + i);
        if (s) s.style.display = i === n ? 'block' : 'none';
        const dot = document.getElementById('dot-' + i);
        const lbl = document.getElementById('lbl-' + i);
        if (dot) { dot.className = i < n ? 'step-dot done' : (i === n ? 'step-dot active' : 'step-dot inactive'); }
        if (lbl) { lbl.className = i <= n ? 'step-label active' : 'step-label inactive'; }
    }
    if (n === 4) {
        document.getElementById('review-guru').textContent = selectedGuruName;
        document.getElementById('review-layanan').textContent = selectedLayananName;
        const titleEl = document.querySelector('input[name="title"]');
        document.getElementById('review-title').textContent = titleEl ? titleEl.value : '—';
    }
    window.scrollTo(0, 0);
}

function selectGuru(el, id) {
    document.querySelectorAll('.guru-opt').forEach(e => {
        e.style.border = '2px solid var(--cream-dark)';
        e.style.background = '';
    });
    el.style.border = '2px solid var(--teal)';
    el.style.background = 'rgba(13,124,102,0.04)';
    document.getElementById('selected-teacher').value = id;
    selectedGuruName = el.querySelector('h4').textContent;
}

function selectLayanan(el, id) {
    document.querySelectorAll('.layanan-opt').forEach(e => {
        e.style.border = '2px solid var(--cream-dark)';
        e.style.background = '';
    });
    el.style.border = '2px solid var(--teal)';
    el.style.background = 'rgba(13,124,102,0.04)';
    document.getElementById('selected-service').value = id;
    selectedLayananName = el.querySelector('p').textContent;
}
</script>
@endpush
