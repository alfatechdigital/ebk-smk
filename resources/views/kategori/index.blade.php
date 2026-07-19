@extends('layouts.app')
@section('title', 'Kategori Layanan')
@section('page-title', 'Kategori Layanan')

@section('content')
    <div class="page-header-row">
        <div class="page-header">
            <h2>Kategori Layanan</h2>
            <p>Kelola kategori layanan konseling yang tersedia</p>
        </div>
        <button class="btn btn-primary" onclick="openTambahKategori()"><i class="fas fa-plus"></i> Tambah Kategori</button>
    </div>

    @php $fallbackColors = ['#0d7c66', '#c8923a', '#3d5454', '#7c5cbf', '#c0392b', '#2c3e50']; @endphp

    <div class="grid-3">
        @forelse ($services as $i => $s)
            @php
                $serviceColor = $s->color ?? $fallbackColors[$i % count($fallbackColors)];
            @endphp
            <div class="card" style="border-top:4px solid {{ $serviceColor }}">
                <div
                    style="width:48px;height:48px;background:{{ $serviceColor }}20;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:{{ $serviceColor }};margin-bottom:14px">
                    <i class="fas {{ Str::startsWith($s->icon, 'fas ') ? Str::after($s->icon, 'fas ') : $s->icon }}"></i>
                </div>
                <h4 style="font-weight:700;font-size:15px;margin-bottom:6px">{{ $s->name }}</h4>
                <p class="text-muted" style="margin-bottom:12px;line-height:1.5">{{ $s->description }}</p>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span
                        class="badge {{ $s->is_active ? 'badge-success' : 'badge-danger' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <div class="action-btns">
                        @php $iconShort = Str::startsWith($s->icon, 'fas ') ? Str::after($s->icon, 'fas ') : $s->icon; @endphp
                        <button class="btn btn-secondary btn-sm"
                            onclick="editKategori({{ $s->id }},'{{ addslashes($s->name) }}','{{ addslashes($s->description) }}','{{ $iconShort }}',{{ $s->is_active ? 'true' : 'false' }},'{{ $serviceColor }}')"><i
                                class="fas fa-edit"></i></button>
                        <form method="POST" id="form-delete-kategori-{{ $s->id }}" action="{{ route('kategori.destroy', $s) }}"
                            style="display:none">@csrf @method('DELETE')</form>
                        <button class="btn btn-danger btn-sm"
                            onclick="openHapusKategori({{ $s->id }}, '{{ addslashes($s->name) }}')" type="button"><i
                                class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column:1/-1"><i class="fas fa-tags"></i>
                <p>Belum ada kategori</p>
            </div>
        @endforelse
    </div>
@endsection

@push('styles')
    <style>
        /* Icon Picker */
        .icon-picker-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-top: 8px;
        }

        .icon-option {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 8px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            background: #f8fafc;
            color: #64748b;
        }

        .icon-option:hover {
            border-color: var(--teal);
            background: #f0fdf4;
            color: var(--teal);
        }

        .icon-option.selected {
            border-color: var(--teal);
            background: var(--teal);
            color: #fff;
        }

        .icon-option i {
            font-size: 20px;
        }

        /* Color Picker */
        .color-picker-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }

        .color-swatch {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
            position: relative;
        }

        .color-swatch:hover {
            transform: scale(1.15);
        }

        .color-swatch.selected {
            border-color: #1e293b;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1e293b;
        }

        .color-swatch.selected::after {
            content: '✓';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
@endpush

@push('modals')
    <div class="modal-overlay" id="modal-kategori">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-header">
                <h3 id="kat-title">Tambah Kategori Layanan</h3>
                <button class="modal-close" onclick="closeModal('modal-kategori')">✕</button>
            </div>
            <form method="POST" id="kat-form" action="{{ route('kategori.store') }}">
                @csrf
                <input type="hidden" name="_method" id="kat-method" value="POST">
                <input type="hidden" name="icon" id="kat-icon-value" value="fa-user">
                <input type="hidden" name="color" id="kat-color-value" value="#0d7c66">

                <div class="field-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="name" id="kat-name" placeholder="Nama layanan konseling" required>
                </div>
                <div class="field-group">
                    <label>Deskripsi</label>
                    <textarea name="description" id="kat-desc"
                        placeholder="Penjelasan singkat tentang layanan ini..."></textarea>
                </div>

                {{-- Icon Picker --}}
                <div class="field-group">
                    <label>Pilih Ikon</label>
                    <div class="icon-picker-grid" id="icon-picker">
                        @foreach(['fa-user', 'fa-users', 'fa-book-open', 'fa-graduation-cap', 'fa-heart', 'fa-lightbulb', 'fa-circle'] as $ico)
                            <div class="icon-option {{ $ico === 'fa-user' ? 'selected' : '' }}" data-icon="{{ $ico }}"
                                onclick="selectIcon('{{ $ico }}')" title="{{ $ico }}">
                                <i class="fas {{ $ico }}"></i>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Color Picker --}}
                <div class="field-group">
                    <label>Pilih Warna</label>
                    <div class="color-picker-grid" id="color-picker">
                        @foreach(['#0d7c66', '#059669', '#0284c7', '#7c3aed', '#c8923a', '#d97706', '#c0392b', '#e11d48', '#3d5454', '#475569', '#1e3a5f', '#92400e'] as $clr)
                            <div class="color-swatch {{ $clr === '#0d7c66' ? 'selected' : '' }}" data-color="{{ $clr }}"
                                style="background:{{ $clr }}" onclick="selectColor('{{ $clr }}')" title="{{ $clr }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="form-row">
                    <div class="field-group">
                        <label>Status</label>
                        <select name="is_active" id="kat-active">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-kategori')">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Simpan Kategori --}}
    <div class="modal-overlay" id="modal-confirm-kategori">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Simpan Kategori Layanan?
            </h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Apakah Anda yakin ingin menyimpan data kategori layanan ini?
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="cancelConfirmKategori()"
                    style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitKategoriForm()"
                    style="margin: 0; background: var(--teal); border-color: var(--teal);"><i class="fas fa-check"></i> Ya,
                    Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus Kategori --}}
    <div class="modal-overlay" id="modal-hapus-kategori">
        <div class="modal" style="max-width: 400px; text-align: center; padding: 24px;">
            <div style="font-size: 3rem; color: #ef4444; margin-bottom: 15px;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--charcoal); margin: 0;">Hapus Kategori?</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 10px; line-height: 1.5;">
                Anda akan menghapus kategori <strong id="hapus-kategori-name"></strong>. Tindakan ini tidak dapat
                dibatalkan.
            </p>
            <div class="modal-footer"
                style="justify-content: center; gap: 10px; border-top: none; padding-top: 20px; margin-top: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-hapus-kategori')"
                    style="margin: 0;">Batal</button>
                <button type="button" class="btn btn-danger" onclick="submitHapusKategori()" style="margin: 0;"><i
                        class="fas fa-trash"></i> Ya, Hapus</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        function openModal(id) { document.getElementById(id).classList.add('open'); }
        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        document.querySelectorAll('.modal-overlay').forEach(m => {
            m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
        });

        // Hapus Kategori Modal
        let _hapusKategoriId = null;
        function openHapusKategori(id, name) {
            _hapusKategoriId = id;
            document.getElementById('hapus-kategori-name').textContent = name;
            openModal('modal-hapus-kategori');
        }
        function submitHapusKategori() {
            if (_hapusKategoriId) {
                document.getElementById('form-delete-kategori-' + _hapusKategoriId).submit();
            }
        }

        // Icon picker
        function selectIcon(ico) {
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            const el = document.querySelector(`.icon-option[data-icon="${ico}"]`);
            if (el) el.classList.add('selected');
            document.getElementById('kat-icon-value').value = ico;
        }

        // Color picker
        function selectColor(clr) {
            document.querySelectorAll('.color-swatch').forEach(el => el.classList.remove('selected'));
            const el = document.querySelector(`.color-swatch[data-color="${clr}"]`);
            if (el) el.classList.add('selected');
            document.getElementById('kat-color-value').value = clr;
        }

        // Open tambah (reset form)
        function openTambahKategori() {
            document.getElementById('kat-title').textContent = 'Tambah Kategori Layanan';
            document.getElementById('kat-form').action = '{{ route('kategori.store') }}';
            document.getElementById('kat-method').value = 'POST';
            document.getElementById('kat-name').value = '';
            document.getElementById('kat-desc').value = '';
            document.getElementById('kat-active').value = '1';
            selectIcon('fa-user');
            selectColor('#0d7c66');
            openModal('modal-kategori');
        }

        // Open edit (populate form)
        function editKategori(id, name, desc, icon, active, color) {
            document.getElementById('kat-title').textContent = 'Edit Kategori';
            document.getElementById('kat-form').action = '/kategori/' + id;
            document.getElementById('kat-method').value = 'PUT';
            document.getElementById('kat-name').value = name;
            document.getElementById('kat-desc').value = desc;
            document.getElementById('kat-active').value = active ? '1' : '0';
            selectIcon(icon || 'fa-user');
            selectColor(color || '#0d7c66');
            openModal('modal-kategori');
        }

        // Intercept submit → show confirm modal
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('kat-form');
            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    openModal('modal-confirm-kategori');
                });
            }
        });

        function cancelConfirmKategori() {
            closeModal('modal-confirm-kategori');
            openModal('modal-kategori');
        }

        window.submitKategoriForm = function () {
            const form = document.getElementById('kat-form');
            if (form) { form.submit(); }
        };
    </script>
@endpush