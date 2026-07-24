<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::transaction(function() use ($rows) {
            $classes = SchoolClass::all()->pluck('id', 'name');

            // 1. Pre-validate for duplicate NIS values
            $nisList = [];
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }
                $nis = !empty($row['nis']) ? trim($row['nis']) : null;
                if ($nis) {
                    // Check duplicate in spreadsheet
                    if (in_array($nis, $nisList)) {
                        throw new \Exception("Ditemukan duplikasi NIS '{$nis}' di dalam file Excel yang Anda unggah.");
                    }
                    $nisList[] = $nis;

                    // Check duplicate in database
                    $exists = Student::where('nis', $nis)->exists();
                    if ($exists) {
                        throw new \Exception("NIS '{$nis}' sudah terdaftar di database. Proses import dibatalkan.");
                    }
                }
            }

            // 2. Perform inserts since validation passed
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }

                // Normalize gender
                $gender = null;
                if (!empty($row['jenis_kelamin'])) {
                    $gk = strtoupper(trim($row['jenis_kelamin']));
                    if ($gk === 'L' || str_starts_with($gk, 'LAKI')) {
                        $gender = 'L';
                    } elseif ($gk === 'P' || str_starts_with($gk, 'PEREMPUAN') || str_starts_with($gk, 'WANITA')) {
                        $gender = 'P';
                    }
                }

                // Normalize class
                $classId = null;
                if (!empty($row['kelas'])) {
                    $classId = $classes[$row['kelas']] ?? null;
                }

                // Read email (generate default if empty)
                $email = $row['email'] ?? null;
                if (empty($email)) {
                    $email = ($row['nis'] ?? uniqid()) . '@ebk.id';
                }

                $nis = !empty($row['nis']) ? trim($row['nis']) : null;

                // Ensure email doesn't duplicate either
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $row['nama'],
                        'email' => $email,
                        'password' => Hash::make($row['password'] ?? 'password123'),
                        'role' => 'siswa',
                        'jenis_kelamin' => $gender,
                        'no_hp' => $row['nomor_hp'] ?? null,
                    ]);
                } else {
                    $user->update([
                        'name' => $row['nama'],
                        'jenis_kelamin' => $gender ?? $user->jenis_kelamin,
                        'no_hp' => $row['nomor_hp'] ?? $user->no_hp,
                    ]);
                    if (!empty($row['password'])) {
                        $user->update([
                            'password' => Hash::make($row['password']),
                        ]);
                    }
                }

                Student::create([
                    'user_id' => $user->id,
                    'nis' => $nis,
                    'class_id' => $classId,
                    'no_hp' => $row['nomor_hp'] ?? null,
                ]);
            }
        });
    }
}
