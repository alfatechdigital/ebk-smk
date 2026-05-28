<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        $notesQuery = \App\Models\CounselingNote::with(['ticket.student.user', 'ticket.student.class', 'teacher.user', 'ticket.service'])
            ->latest();

        if ($teacher) {
            $notesQuery->where('teacher_id', $teacher->id);
        }

        $notes = $notesQuery->get();

        // Per siswa
        $perSiswa = Student::with(['user', 'class', 'tickets.service'])
            ->whereHas('tickets', fn($q) => $teacher ? $q->where('teacher_id', $teacher->id) : $q)
            ->get()
            ->map(fn($s) => [
                'name'     => $s->user->name,
                'kelas'    => $s->class->name ?? '-',
                'total'    => $s->tickets->count(),
                'selesai'  => $s->tickets->where('status','selesai')->count(),
                'kategori' => $s->tickets->sortByDesc(fn($t) => $t->service_id)->first()?->service?->name ?? '-',
                'status'   => $s->tickets->whereIn('status',['menunggu','diproses'])->count() > 0 ? 'Aktif' : 'Selesai',
            ]);

        // Per kelas
        $perKelas = SchoolClass::with(['students.tickets'])->get()->map(fn($k) => [
            'name'          => $k->name,
            'wali_kelas'    => $k->wali_kelas ?? '-',
            'total_siswa'   => $k->students->count(),
            'pernah'        => $k->students->filter(fn($s) => $s->tickets->count() > 0)->count(),
            'total_kasus'   => $k->students->sum(fn($s) => $s->tickets->count()),
            'persen'        => $k->students->count() > 0
                ? round($k->students->filter(fn($s) => $s->tickets->count() > 0)->count() / $k->students->count() * 100) . '%'
                : '0%',
        ]);

        // Per bulan
        $perBulan = Ticket::selectRaw("strftime('%Y-%m', created_at) as bulan, count(*) as total, sum(status='selesai') as selesai")
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        return view('rekap.index', compact('perSiswa', 'perKelas', 'perBulan', 'notes'));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $teacherId = ($user->role !== 'admin' && $user->role !== 'superadmin' && $user->teacher) 
            ? $user->teacher->id 
            : $request->teacher_id;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\JurnalExport($teacherId, $request->bulan), 
            'Jurnal_Kegiatan_BK.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $teacherId = ($user->role !== 'admin' && $user->role !== 'superadmin' && $user->teacher) 
            ? $user->teacher->id 
            : $request->teacher_id;

        $query = \App\Models\CounselingNote::with(['ticket.student.user', 'ticket.student.class', 'teacher.user', 'ticket.service'])->latest();

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        if ($request->bulan) {
            $query->whereMonth('created_at', date('m', strtotime($request->bulan)))
                  ->whereYear('created_at', date('Y', strtotime($request->bulan)));
        }

        $notes = $query->get();
        $teacher = $teacherId ? \App\Models\Teacher::with('user')->find($teacherId) : null;
        $month = $request->bulan ? date('F Y', strtotime($request->bulan)) : 'Semua Bulan';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap_jurnal_catatan', compact('notes', 'teacher', 'month'))->setPaper('a4', 'landscape');
        return $pdf->download('Jurnal_Kegiatan_BK.pdf');
    }
}
