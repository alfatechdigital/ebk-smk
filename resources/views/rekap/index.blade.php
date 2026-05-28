@extends('layouts.app')
@section('title', 'Jurnal Kegiatan BK')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Scoped overrides to protect main layout */
        .sidebar { z-index: 1040; }
        .topbar { z-index: 1030; }
        .text-teal { color: #0d7c66 !important; }
        .btn-teal { background-color: #0d7c66; color: white; border-color: #0d7c66; }
        .btn-teal:hover { background-color: #085e4d; color: white; border-color: #085e4d; }
        
        /* Bootstrap modal fixes over custom layout */
        .modal-backdrop { z-index: 1050; }
        .modal { z-index: 1055; }
        
        @media print {
            .no-print { display: none !important; }
            .card-header { display: none !important; }
            .topbar, .sidebar { display: none !important; }
            .main-content { margin: 0 !important; padding: 0 !important; }
            table { width: 100% !important; border-collapse: collapse; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Jurnal Kegiatan BK</h2>
            <p class="text-muted mb-0">Daftar aktivitas bimbingan dan capaian layanan konseling seluruh Guru BK</p>
        </div>
    </div>

    <div x-data="jurnalFilter()">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="card-title m-0 fw-bold">Log Aktivitas Konseling</h5>
                
                <div class="d-flex gap-2 align-items-center flex-wrap flex-grow-1 justify-content-md-end">
                    
                    {{-- Form Export Excel --}}
                    <form action="{{ route('rekap.export.excel') }}" method="GET" class="d-inline m-0">
                        <input type="hidden" name="bulan" x-bind:value="selectedBulan">
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            <i class="fa fa-file-excel me-1"></i> Excel
                        </button>
                    </form>

                    {{-- Form Export PDF --}}
                    <form action="{{ route('rekap.export.pdf') }}" method="GET" class="d-inline m-0">
                        <input type="hidden" name="bulan" x-bind:value="selectedBulan">
                        <button type="submit" class="btn btn-danger btn-sm px-3">
                            <i class="fa fa-file-pdf me-1"></i> PDF
                        </button>
                    </form>

                    {{-- Filter Bulan --}}
                    <select class="form-select form-select-sm w-auto" x-model="selectedBulan" @change="resetSubFilters()">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Guru (Admin/Superadmin) --}}
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                    <select class="form-select form-select-sm w-auto" x-model="selectedGuru" @change="selectedSiswa = ''">
                        <option value="">Semua Guru BK</option>
                        <template x-for="gru in availableTeachers" :key="gru">
                            <option :value="gru" x-text="gru"></option>
                        </template>
                    </select>
                    @endif

                    <select class="form-select form-select-sm w-auto" x-model="selectedKelas" @change="selectedSiswa = ''">
                        <option value="">Semua Kelas</option>
                        <template x-for="kls in availableClasses" :key="kls">
                            <option :value="kls" x-text="kls"></option>
                        </template>
                    </select>

                    <select class="form-select form-select-sm w-auto" x-model="selectedSiswa">
                        <option value="">Semua Siswa</option>
                        <template x-for="sis in availableStudents" :key="sis">
                            <option :value="sis" x-text="sis"></option>
                        </template>
                    </select>
<!-- 
                    <button class="btn btn-primary btn-sm px-3" onclick="window.print()">
                        <i class="fa fa-print me-1"></i> Print
                    </button> -->
                </div>
            </div>

            <div class="card-body p-0 mt-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-jurnal">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Hari/Tgl</th>
                                <th>Identitas Siswa</th>
                                <th>Guru BK</th>
                                <th>Masalah / Akar Masalah</th>
                                <th>Tindakan / Capaian</th>
                                <th class="text-center no-print pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $namaBulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                            @endphp

                            @forelse ($notes as $note)
                            @php
                                $bulanNama = $namaBulanIndo[$note->created_at->format('n')]; 
                                $namaSiswa = $note->ticket->student->user->name ?? 'Tidak ada data';
                                $kelasSiswa = $note->ticket->student->class->name ?? '-';
                                $namaGuru = $note->teacher->user->name ?? 'Guru Tidak Teridentifikasi';
                                $ticketId = $note->ticket_id;
                            @endphp
                            <tr class="jurnal-row" 
                                x-show="shouldShow('{{ $bulanNama }}', '{{ $kelasSiswa }}', '{{ $namaSiswa }}', '{{ $namaGuru }}')"
                                data-bulan="{{ $bulanNama }}" 
                                data-kelas="{{ $kelasSiswa }}" 
                                data-siswa="{{ $namaSiswa }}"
                                data-guru="{{ $namaGuru }}">
                                
                                <td class="px-4 text-nowrap">
                                    <span class="fw-medium">{{ $note->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $namaSiswa }}</div>
                                    <div class="small text-muted">{{ $kelasSiswa }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-teal border">{{ $namaGuru }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $note->title }}</div>
                                    <div class="small text-muted text-wrap" style="max-width: 250px;">{{ \Illuminate\Support\Str::limit($note->masalah, 70) }}</div>
                                </td>
                                <td>
                                    <div class="small text-dark text-wrap" style="max-width: 250px;">{{ \Illuminate\Support\Str::limit($note->tindakan, 70) }}</div>
                                </td>
                                <td class="text-center no-print pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="{{ route('chat.show', $ticketId) }}" class="btn btn-sm btn-outline-primary" title="Lihat Chat & Riwayat">
                                            <i class="fa fa-comments"></i>
                                        </a>
                                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' || auth()->user()->teacher?->id === $note->teacher_id)
                                        <button onclick="editNote({{ $note->id }}, `{{ addslashes($note->title) }}`, `{{ addslashes($note->masalah) }}`, `{{ addslashes($note->tindakan) }}`, `{{ addslashes($note->kesimpulan) }}`)" class="btn btn-sm btn-outline-warning" title="Edit Jurnal">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <form action="{{ route('catatan.destroy', $note->id) }}" method="POST" id="form-delete-{{ $note->id }}" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button onclick="confirmDelete({{ $note->id }})" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-muted text-center py-4">Belum ada data jurnal kegiatan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit (Bootstrap) -->
<div class="modal fade" id="modalEditNote" tabindex="-1" aria-labelledby="modalEditNoteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="modalEditNoteLabel">Edit Catatan Konseling</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" id="edit-note-form">
          @csrf
          @method('PUT')
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary small">Judul / Topik</label>
                  <input type="text" class="form-control" name="title" id="edit-title" required>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary small">Masalah / Akar Masalah</label>
                  <textarea class="form-control" name="masalah" id="edit-masalah" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary small">Tindakan / Solusi</label>
                  <textarea class="form-control" name="tindakan" id="edit-tindakan" rows="3" required></textarea>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary small">Kesimpulan (Opsional)</label>
                  <textarea class="form-control" name="kesimpulan" id="edit-kesimpulan" rows="2"></textarea>
              </div>
          </div>
          <div class="modal-footer bg-light border-top-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-teal">Simpan Perubahan</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
<script>
function jurnalFilter() {
    return {
        selectedBulan: '',
        selectedKelas: '',
        selectedSiswa: '',
        selectedGuru: '',
        allRows: [],

        init() {
            this.$nextTick(() => {
                this.allRows = Array.from(document.querySelectorAll('.jurnal-row')).map(row => ({
                    bulan: row.getAttribute('data-bulan'),
                    kelas: row.getAttribute('data-kelas'),
                    siswa: row.getAttribute('data-siswa'),
                    guru: row.getAttribute('data-guru')
                }));
            });
        },

        resetSubFilters() {
            this.selectedKelas = '';
            this.selectedSiswa = '';
            this.selectedGuru = '';
        },

        get availableTeachers() {
            let teachers = this.allRows
                .filter(row => !this.selectedBulan || row.bulan === this.selectedBulan)
                .map(row => row.guru);
            return [...new Set(teachers)].sort();
        },

        get availableClasses() {
            let classes = this.allRows
                .filter(row => {
                    return (!this.selectedBulan || row.bulan === this.selectedBulan) &&
                           (!this.selectedGuru || row.guru === this.selectedGuru);
                })
                .map(row => row.kelas);
            return [...new Set(classes)].sort();
        },

        get availableStudents() {
            let students = this.allRows
                .filter(row => {
                    return (!this.selectedBulan || row.bulan === this.selectedBulan) &&
                           (!this.selectedGuru || row.guru === this.selectedGuru) &&
                           (!this.selectedKelas || row.kelas === this.selectedKelas);
                })
                .map(row => row.siswa);
            return [...new Set(students)].sort();
        },

        shouldShow(bulan, kelas, siswa, guru) {
            let matchBulan = !this.selectedBulan || bulan === this.selectedBulan;
            let matchKelas = !this.selectedKelas || kelas === this.selectedKelas;
            let matchSiswa = !this.selectedSiswa || siswa === this.selectedSiswa;
            let matchGuru = !this.selectedGuru || guru === this.selectedGuru;
            return matchBulan && matchKelas && matchSiswa && matchGuru;
        }
    }
}

function confirmDelete(id) {
    if(confirm('Apakah Anda yakin ingin menghapus data jurnal ini?')) {
        document.getElementById('form-delete-' + id).submit();
    }
}

function editNote(id, title, masalah, tindakan, kesimpulan) {
    document.getElementById('edit-note-form').action = '/catatan/' + id;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-masalah').value = masalah;
    document.getElementById('edit-tindakan').value = tindakan;
    document.getElementById('edit-kesimpulan').value = kesimpulan || '';
    
    // Show Bootstrap Modal
    var myModal = new bootstrap.Modal(document.getElementById('modalEditNote'));
    myModal.show();
}
</script>
@endpush