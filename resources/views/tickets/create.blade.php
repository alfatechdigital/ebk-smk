@extends('layouts.app')
@section('title', 'Ajukan Konsultasi')
@section('page-title', 'Ajukan Konsultasi')

@section('content')
<div class="page-header">
    <h2>Ajukan Konsultasi Baru</h2>
    <p>Ceritakan masalahmu kepada Guru BK terpercaya</p>
</div>
<div class="warning-box mb-20"><i class="fas fa-shield-alt"></i><span>Konsultasi ini bersifat <b>sepenuhnya rahasia</b>. Guru BK berkomitmen menjaga privasi kamu.</span></div>

<!-- Step Indicator -->
<div class="step-wizard">
    <div style="flex:1;text-align:center">
        <div class="step-dot active" id="step1-dot">1</div>
        <p class="step-label active" id="step1-lbl">Jenis Layanan</p>
    </div>
    <div class="step-line"><div></div></div>
    <div style="flex:1;text-align:center">
        <div class="step-dot inactive" id="step2-dot">2</div>
        <p class="step-label inactive" id="step2-lbl">Detail Konsultasi</p>
    </div>
    <div class="step-line"><div></div></div>
    <div style="flex:1;text-align:center">
        <div class="step-dot inactive" id="step3-dot">3</div>
        <p class="step-label inactive" id="step3-lbl">Submit</p>
    </div>
</div>

<form method="POST" action="{{ route('tickets.store') }}" id="form-ajukan">
@csrf

<!-- Step 1: Jenis Layanan -->
<div id="ajukan-step1">
    <div class="card">
        <div class="card-header"><div class="card-title">Pilih Jenis Layanan Konseling</div></div>
        <div class="grid-3" style="margin-bottom:20px">
            @foreach ($services as $s)
            <div class="service-option" onclick="selectLayanan(this, {{ $s->id }})" style="border:2px solid var(--cream-dark);border-radius:var(--radius);padding:16px;cursor:pointer;text-align:center">
                <i class="{{ $s->icon }}" style="font-size:28px;color:{{ $s->color }};margin-bottom:8px;display:block"></i>
                <p style="font-weight:700;font-size:14px">{{ $s->name }}</p>
                <p class="text-muted" style="font-size:12px;margin-top:4px">{{ Str::limit($s->description, 40) }}</p>
            </div>
            @endforeach
        </div>
        <input type="hidden" name="service_id" id="selected-service" value="{{ $services->first()?->id }}">
        <div style="display:flex;justify-content:flex-end">
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Selanjutnya <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

<!-- Step 2: Detail -->
<div id="ajukan-step2" style="display:none">
    <div class="card">
        <div class="card-header"><div class="card-title">Detail Konsultasi</div></div>
        <div class="field-group"><label>Judul / Topik Masalah</label><input type="text" name="title" placeholder="Contoh: Masalah kepercayaan diri saat presentasi" required></div>
        <div class="field-group"><label>Ceritakan masalahmu secara detail <span style="color:var(--teal)">(Rahasia)</span></label><textarea name="description" style="min-height:150px" placeholder="Ceritakan apa yang sedang kamu alami..." required></textarea></div>
        <div class="field-group"><label>Tindakan yang sudah kamu lakukan <span style="color:var(--muted);font-weight:400">(opsional)</span></label><textarea name="prior_action" placeholder="Apakah kamu sudah mencoba melakukan sesuatu?"></textarea></div>
        <div class="field-group" style="background:var(--cream);padding:14px;border-radius:var(--radius-sm);display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <input type="checkbox" name="anonymous" id="anonymous" value="1" style="width:18px;height:18px;accent-color:var(--teal);cursor:pointer;">
            <label for="anonymous" style="margin:0;cursor:pointer;">Ajukan secara Anonim <span style="font-weight:400;color:var(--muted);font-size:12px;display:block;">Identitasmu akan ditandai anonim di sistem, namun Guru BK tetap dapat melihat namamu dengan label Anonim untuk keperluan tindak lanjut.</span></label>
        </div>
        <div style="display:flex;gap:10px;justify-content:space-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(1)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(3)">Review & Submit <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</div>

<!-- Step 3: Review -->
<div id="ajukan-step3" style="display:none">
    <div class="card">
        <div class="card-header"><div class="card-title">Review & Konfirmasi</div></div>
        <div style="background:var(--cream);border-radius:var(--radius-sm);padding:20px;margin-bottom:20px">
            <div class="profile-info-grid">
                <div class="profile-info-item"><label>Jenis Layanan</label><p id="review-service">-</p></div>
                <div class="profile-info-item"><label>Topik Masalah</label><p id="review-title">-</p></div>
            </div>
        </div>
        <div class="info-box mb-20"><i class="fas fa-lock"></i><span>Konsultasi ini akan otomatis diteruskan ke Guru BK yang ditugaskan untuk kelas Anda.</span></div>
        <div style="display:flex;gap:10px;justify-content:space-between">
            <button type="button" class="btn btn-secondary" onclick="nextStep(2)"><i class="fas fa-arrow-left"></i> Kembali</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Konsultasi</button>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let currentStep = 1;
function nextStep(n) {
    if (n === 3 && currentStep === 2) {
        const title = document.querySelector('[name=title]');
        const desc = document.querySelector('[name=description]');
        
        let isValid = true;
        if (!title.value.trim()) {
            title.style.borderColor = 'var(--danger)';
            isValid = false;
        } else {
            title.style.borderColor = '';
        }
        
        if (!desc.value.trim()) {
            desc.style.borderColor = 'var(--danger)';
            isValid = false;
        } else {
            desc.style.borderColor = '';
        }
        
        if (!isValid) {
            alert('Judul dan Deskripsi Masalah wajib diisi!');
            if (!title.value.trim()) title.focus();
            else desc.focus();
            return;
        }
    }
    for (let i=1; i<=3; i++) {
        const s = document.getElementById('ajukan-step'+i);
        if (s) s.style.display = i===n ? 'block' : 'none';
        const dot = document.getElementById('step'+i+'-dot');
        const lbl = document.getElementById('step'+i+'-lbl');
        if (dot) { dot.className = 'step-dot ' + (i<=n ? 'active' : 'inactive'); }
        if (lbl) { lbl.className = 'step-label ' + (i<=n ? 'active' : 'inactive'); }
    }
    currentStep = n;
    if (n === 3) updateReview();
}
function selectLayanan(el, id) {
    document.querySelectorAll('.service-option').forEach(e => { e.style.border='2px solid var(--cream-dark)'; e.style.background=''; });
    el.style.border='2px solid var(--teal)'; el.style.background='rgba(13,124,102,0.04)';
    document.getElementById('selected-service').value = id;
}
function updateReview() {
    const svc  = document.querySelector('.service-option[style*="var(--teal)"] p[style*="font-weight:700"]');
    document.getElementById('review-service').textContent = svc ? svc.textContent : '-';
    document.getElementById('review-title').textContent = document.querySelector('[name=title]').value || '-';
}
// Auto-select first service
document.querySelector('.service-option')?.click();
</script>
@endpush
