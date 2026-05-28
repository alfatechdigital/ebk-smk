<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $classes = SchoolClass::all()->pluck('id', 'name');

        foreach ($rows as $row) {
            if (empty($row['nama']) || empty($row['email'])) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama'],
                    'password' => Hash::make($row['password'] ?? 'password123'),
                    'role' => 'siswa'
                ]
            );

            $classId = null;
            if (!empty($row['kelas'])) {
                $classId = $classes[$row['kelas']] ?? null;
            }

            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nis' => $row['nis'] ?? null,
                    'class_id' => $classId
                ]
            );
        }
    }
}
