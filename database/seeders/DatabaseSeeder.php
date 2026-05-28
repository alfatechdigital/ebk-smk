<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institute;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\CounselingNote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Institute ──
        $institute = Institute::create([
            'name' => 'SMK Negeri 1 Contoh',
            'npsn' => '12345678',
            'address' => 'Jl. Pendidikan No. 1, Kota Contoh, Jawa Timur',
            'phone' => '(0341) 123456',
            'email' => 'info@smkn1contoh.sch.id',
            'kepala_sekolah' => 'Drs. Agus Suprianto, M.Pd',
            'tahun_ajaran' => '2024/2025',
            'semester' => 'Ganjil',
        ]);

        // ── Classes ──
        $classes = [];
        foreach (['X IPA 1','X IPS 1','X IPS 2','XI IPA 1','XI IPA 2','XI IPS 1','XII IPA 1'] as $name) {
            $classes[] = SchoolClass::create(['name' => $name, 'institute_id' => $institute->id, 'wali_kelas' => 'Guru Wali ' . $name]);
        }

        // ── Services ──
        $svcData = [
            ['name' => 'Konseling Individual', 'description' => 'Layanan konseling satu-satu antara siswa dan guru BK', 'icon' => 'fas fa-user', 'color' => '#0d7c66'],
            ['name' => 'Konseling Kelompok',   'description' => 'Layanan konseling yang melibatkan beberapa siswa sekaligus', 'icon' => 'fas fa-users', 'color' => '#c8923a'],
            ['name' => 'Konseling Karir',      'description' => 'Bimbingan untuk perencanaan karir dan pemilihan jurusan', 'icon' => 'fas fa-graduation-cap', 'color' => '#3d5454'],
            ['name' => 'Konseling Akademik',   'description' => 'Bimbingan masalah belajar, nilai, dan kesulitan akademik', 'icon' => 'fas fa-book-open', 'color' => '#7c5cbf'],
            ['name' => 'Konseling Pribadi',    'description' => 'Masalah pribadi, keluarga, dan sosial-emosional', 'icon' => 'fas fa-heart', 'color' => '#c0392b'],
        ];
        $services = [];
        foreach ($svcData as $s) { $services[] = Service::create($s); }

        // ── Users ──
        $password = Hash::make('password');

        // SuperAdmin
        User::create(['name' => 'Super Admin', 'email' => 'superadmin@ebk.id', 'password' => $password, 'role' => 'superadmin']);

        // Admin
        User::create(['name' => 'Ahmad Fauzi', 'email' => 'admin@ebk.id', 'password' => $password, 'role' => 'admin']);

        // Guru BK 1
        $guruUser1 = User::create(['name' => 'Hendra Kurniawan, S.Pd', 'email' => 'guru@ebk.id', 'password' => $password, 'role' => 'guru']);
        $teacher1 = Teacher::create(['user_id' => $guruUser1->id, 'institute_id' => $institute->id, 'nip' => '198801012015', 'spesialisasi' => 'Spesialis: Individual & Karir', 'no_whatsapp' => '081234567890']);

        // Guru BK 2
        $guruUser2 = User::create(['name' => 'Ratna Dewi, S.Psi', 'email' => 'guru2@ebk.id', 'password' => $password, 'role' => 'guru']);
        $teacher2 = Teacher::create(['user_id' => $guruUser2->id, 'institute_id' => $institute->id, 'nip' => '199203052017', 'spesialisasi' => 'Spesialis: Kelompok & Akademik', 'no_whatsapp' => '081234567891']);

        // Assign Guru to Classes
        foreach ($classes as $idx => $class) {
            $class->update(['teacher_id' => $idx % 2 === 0 ? $teacher1->id : $teacher2->id]);
        }

        // Siswa 1 - Rafi
        $siswaUser1 = User::create(['name' => 'Rafi Ahmad', 'email' => 'siswa@ebk.id', 'password' => $password, 'role' => 'siswa']);
        $student1 = Student::create(['user_id' => $siswaUser1->id, 'class_id' => $classes[4]->id, 'nis' => '2024003']); // XI IPA 2

        // Siswa 2 - Siti
        $siswaUser2 = User::create(['name' => 'Siti Rahayu', 'email' => 'siti@ebk.id', 'password' => $password, 'role' => 'siswa']);
        $student2 = Student::create(['user_id' => $siswaUser2->id, 'class_id' => $classes[3]->id, 'nis' => '2024001']); // XI IPA 1

        // Siswa 3 - Budi
        $siswaUser3 = User::create(['name' => 'Budi Santoso', 'email' => 'budi@ebk.id', 'password' => $password, 'role' => 'siswa']);
        $student3 = Student::create(['user_id' => $siswaUser3->id, 'class_id' => $classes[2]->id, 'nis' => '2024002']); // X IPS 2

        // Siswa 4 - Dewi
        $siswaUser4 = User::create(['name' => 'Dewi Safitri', 'email' => 'dewi@ebk.id', 'password' => $password, 'role' => 'siswa']);
        $student4 = Student::create(['user_id' => $siswaUser4->id, 'class_id' => $classes[3]->id, 'nis' => '2024004']);

        // Siswa 5 - Andi
        $siswaUser5 = User::create(['name' => 'Andi Rahmat', 'email' => 'andi@ebk.id', 'password' => $password, 'role' => 'siswa']);
        $student5 = Student::create(['user_id' => $siswaUser5->id, 'class_id' => $classes[0]->id, 'nis' => '2024005']);

        // ── Tickets ──
        // Ticket 1 - Rafi (menunggu)
        $t1 = Ticket::create([
            'code' => '#TK-2024-001', 'student_id' => $student1->id, 'service_id' => $services[0]->id,
            'teacher_id' => $teacher1->id, 'status' => 'menunggu', 'title' => 'Masalah Kepercayaan Diri di Kelas',
            'description' => 'Saya merasa tidak percaya diri saat presentasi di depan kelas dan sering menghindari situasi tersebut...',
        ]);

        // Messages for t1
        TicketMessage::create(['ticket_id' => $t1->id, 'sender_id' => $siswaUser1->id, 'type' => 'text', 'content' => 'Selamat pagi Pak Hendra. Saya ingin berkonsultasi tentang masalah yang sudah lama saya simpan.', 'created_at' => now()->subHours(3)]);
        TicketMessage::create(['ticket_id' => $t1->id, 'sender_id' => $guruUser1->id, 'type' => 'text', 'content' => 'Selamat pagi Rafi. Tentu, silakan ceritakan. Apapun yang kamu sampaikan di sini akan saya jaga kerahasiaannya. 😊', 'created_at' => now()->subHours(3)->addMinutes(3)]);
        TicketMessage::create(['ticket_id' => $t1->id, 'sender_id' => $siswaUser1->id, 'type' => 'text', 'content' => 'Saya sering merasa cemas berlebihan saat ada presentasi di kelas. Bahkan beberapa hari sebelumnya pun sudah tidak bisa tidur.', 'created_at' => now()->subHours(3)->addMinutes(5)]);
        TicketMessage::create(['ticket_id' => $t1->id, 'sender_id' => $guruUser1->id, 'type' => 'text', 'content' => 'Terima kasih sudah mau berbagi, Rafi. Rasa cemas seperti itu wajar dan bisa diatasi. Bisa ceritakan lebih detail, kira-kira sejak kapan kamu merasakan ini?', 'created_at' => now()->subHours(3)->addMinutes(8)]);
        TicketMessage::create(['ticket_id' => $t1->id, 'sender_id' => $siswaUser1->id, 'type' => 'text', 'content' => 'Saya masih belum bisa tidur pak, dan besok ada presentasi lagi...', 'created_at' => now()->subHours(2)]);

        // Ticket 2 - Dewi (diproses)
        $t2 = Ticket::create([
            'code' => '#TK-2024-002', 'student_id' => $student4->id, 'service_id' => $services[2]->id,
            'teacher_id' => $teacher2->id, 'status' => 'diproses', 'title' => 'Bingung Memilih Jurusan Kuliah',
            'description' => 'Saya masih bingung ingin melanjutkan ke jurusan apa, antara teknik informatika atau bisnis manajemen...',
        ]);

        TicketMessage::create(['ticket_id' => $t2->id, 'sender_id' => $siswaUser4->id, 'type' => 'text', 'content' => 'Bu Ratna, saya bingung mau pilih jurusan apa di kuliah nanti.', 'created_at' => now()->subDay()]);
        TicketMessage::create(['ticket_id' => $t2->id, 'sender_id' => $guruUser2->id, 'type' => 'text', 'content' => 'Hai Dewi, ayo kita diskusikan bersama. Ceritakan minat dan bakatmu ya.', 'created_at' => now()->subDay()->addMinutes(10)]);

        // Ticket 3 - Siti (selesai)
        $t3 = Ticket::create([
            'code' => '#TK-2024-003', 'student_id' => $student2->id, 'service_id' => $services[1]->id,
            'teacher_id' => $teacher1->id, 'status' => 'selesai', 'title' => 'Konflik dengan Teman Sekelas',
            'description' => 'Saya dan teman saya sempat berselisih paham terkait tugas kelompok dan hubungan kami menjadi renggang...',
            'completed_at' => now()->subDays(3),
        ]);

        // Counseling Note for t3
        CounselingNote::create([
            'ticket_id' => $t3->id, 'teacher_id' => $teacher1->id, 'title' => 'Konflik Pertemanan',
            'masalah' => 'Terdapat perselisihan antara siswa dengan sahabat dekatnya akibat kesalahpahaman komunikasi dalam tugas kelompok.',
            'tindakan' => 'Dilakukan mediasi virtual dengan pendekatan komunikasi asertif. Siswa diarahkan untuk melakukan pendekatan rekonsiliasi secara mandiri.',
        ]);

        // Ticket 4 - Budi (menunggu)
        Ticket::create([
            'code' => '#TK-2024-004', 'student_id' => $student3->id, 'service_id' => $services[3]->id,
            'teacher_id' => $teacher2->id, 'status' => 'menunggu', 'title' => 'Kesulitan Belajar Matematika',
            'description' => 'Nilai matematika saya terus menurun dan saya tidak mengerti cara memahami materi dengan baik...',
        ]);

        // Ticket 5 - Andi (selesai)
        Ticket::create([
            'code' => '#TK-2024-005', 'student_id' => $student5->id, 'service_id' => $services[4]->id,
            'teacher_id' => $teacher1->id, 'status' => 'selesai', 'title' => 'Masalah Keluarga',
            'description' => 'Saya mengalami tekanan dari keluarga terkait harapan orang tua terhadap nilai sekolah saya...',
            'completed_at' => now()->subWeek(),
        ]);
    }
}
