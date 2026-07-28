<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_siswa' => 0,
            'total_siswa_x' => 0,
            'total_siswa_xi' => 0,
            'total_siswa_xii' => 0,
            'total_layanan' => \App\Models\Service::count(),
            'total_konsultasi' => 0,
            'menunggu' => 0,
            'diproses' => 0,
        ];

        $recentTickets = collect();
        $assignedGuru = null;
        $classActivities = collect();
        $teachers = collect();

        if ($user->isAdmin()) {
            $stats['total_siswa'] = Student::count();
            $stats['total_siswa_x'] = Student::whereHas('class', function($q) {
                $q->where('name', 'like', 'X %')
                  ->where('name', 'not like', 'XI %')
                  ->where('name', 'not like', 'XII %');
            })->count();
            $stats['total_siswa_xi'] = Student::whereHas('class', function($q) {
                $q->where('name', 'like', 'XI %')
                  ->where('name', 'not like', 'XII %');
            })->count();
            $stats['total_siswa_xii'] = Student::whereHas('class', function($q) {
                $q->where('name', 'like', 'XII %');
            })->count();

            $stats['menunggu'] = Ticket::where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('status', 'diproses')->count();
            $stats['total_konsultasi'] = $stats['menunggu'] + $stats['diproses'];
            $recentTickets = Ticket::with(['student.user', 'service'])->latest()->take(5)->get();
            $teachers = Teacher::with(['user', 'classes'])->get();
        } elseif ($user->isGuru()) {
            $teacherId = $user->teacher->id;
            // Total siswa in classes mentored by this Guru
            $stats['total_siswa'] = Student::whereHas('class', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count();
            $stats['menunggu'] = Ticket::where('teacher_id', $teacherId)->where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('teacher_id', $teacherId)->where('status', 'diproses')->count();
            $stats['total_konsultasi'] = $stats['menunggu'] + $stats['diproses'];
            $recentTickets = Ticket::with(['student.user', 'service'])->where('teacher_id', $teacherId)->latest()->take(5)->get();
 
            // Activity per class mentored by this Guru
            $classes = SchoolClass::where('teacher_id', $teacherId)->get();
            foreach ($classes as $cls) {
                $count = Ticket::whereHas('student', function($q) use ($cls) {
                    $q->where('class_id', $cls->id);
                })->count();
                $classActivities->push(['class_name' => $cls->name, 'count' => $count]);
            }
        } elseif ($user->isSiswa()) {
            $studentId = $user->student->id;
            $stats['menunggu'] = Ticket::where('student_id', $studentId)->where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('student_id', $studentId)->where('status', 'diproses')->count();
            $stats['total_konsultasi'] = $stats['menunggu'] + $stats['diproses'];
            $recentTickets = Ticket::with(['teacher.user', 'service'])->where('student_id', $studentId)->latest()->take(5)->get();
            $assignedGuru = $user->student->class->teacher->user->name ?? 'Belum ditugaskan';
        }

        $activities = $this->getActivities($user);

        return view('dashboard', compact('stats', 'recentTickets', 'assignedGuru', 'classActivities', 'activities', 'teachers'));
    }

    private function getActivities(User $user): array
    {
        // Latest ticket messages as activity log
        $tickets = Ticket::with(['student.user', 'teacher.user', 'service'])
            ->latest()->limit(4)->get();

        return $tickets->map(fn($t) => [
            'icon'  => 'fas fa-ticket-alt',
            'color' => 'teal',
            'text'  => 'Tiket ' . $t->code . ' - ' . $t->title,
            'time'  => $t->created_at->diffForHumans(),
        ])->toArray();
    }
}
