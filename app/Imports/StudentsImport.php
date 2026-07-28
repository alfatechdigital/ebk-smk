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
    protected $forceUpdate;

    public function __construct($forceUpdate = false)
    {
        $this->forceUpdate = $forceUpdate;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use ($rows) {
            $classes = SchoolClass::all()->pluck('id', 'name');

            // Temporarily lower Bcrypt cost to minimum (4) to speed up bulk hashing in this request
            if (app('hash')->driver() instanceof \Illuminate\Hashing\BcryptHasher) {
                app('hash')->driver()->setRounds(4);
            }

            // Pre-calculate hash for default password to prevent CPU bottleneck on bulk imports
            $defaultHash = Hash::make('password123');
            $customHashes = [];

            $getPasswordHash = function($rowPassword) use ($defaultHash, &$customHashes) {
                $passwordVal = !empty($rowPassword) ? trim($rowPassword) : 'password123';
                if ($passwordVal === 'password123') {
                    return $defaultHash;
                }
                if (!isset($customHashes[$passwordVal])) {
                    $customHashes[$passwordVal] = Hash::make($passwordVal);
                }
                return $customHashes[$passwordVal];
            };

            // 1. Pre-validate for duplicate NIS values
            $nisList = [];
            $emailList = [];
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }
                $nis = !empty($row['nis']) ? trim($row['nis']) : null;
                $email = !empty($row['email']) ? trim($row['email']) : null;
                if ($nis) {
                    // Check duplicate in spreadsheet
                    if (in_array($nis, $nisList)) {
                        throw new \Exception("Ditemukan duplikasi NIS '{$nis}' di dalam file Excel yang Anda unggah.");
                    }
                    $nisList[] = $nis;
                }
                if ($email) {
                    $emailList[] = $email;
                }
            }

            // Batch database duplicate check (only if not forcing update)
            if (!$this->forceUpdate && !empty($nisList)) {
                $duplicateNis = Student::whereIn('nis', $nisList)->pluck('nis')->first();
                if ($duplicateNis) {
                    throw new \Exception("NIS '{$duplicateNis}' sudah terdaftar di database. Proses import dibatalkan.");
                }
            }

            // Batch load existing students and users to prevent N+1 query bottlenecks in loop
            $existingStudents = Student::whereIn('nis', $nisList)->with('user')->get()->keyBy('nis');
            $existingUsersByEmail = User::whereIn('email', $emailList)->get()->keyBy('email');

            // 2. Perform inserts/updates since validation passed
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

                // Read email (keep null if empty)
                $email = !empty($row['email']) ? trim($row['email']) : null;
                $nis = !empty($row['nis']) ? trim($row['nis']) : null;

                // Check if student with same NIS already exists (using in-memory collection map)
                $existingStudent = null;
                if ($nis) {
                    $existingStudent = $existingStudents->get($nis);
                }

                if ($existingStudent) {
                    // Update existing student and user
                    $user = $existingStudent->user;
                    if ($user) {
                        $userData = [
                            'name' => $row['nama'],
                            'jenis_kelamin' => $gender ?? $user->jenis_kelamin,
                            'no_hp' => $row['nomor_hp'] ?? $user->no_hp,
                        ];
                        if ($email) {
                            $userData['email'] = $email;
                        }
                        $user->update($userData);

                        if (!empty($row['password'])) {
                            $user->update([
                                'password' => $getPasswordHash($row['password']),
                            ]);
                        }
                    }

                    $existingStudent->update([
                        'class_id' => $classId,
                        'no_hp' => $row['nomor_hp'] ?? $existingStudent->no_hp,
                    ]);
                } else {
                    // Ensure email doesn't duplicate for new users (using in-memory collection map)
                    $user = null;
                    if ($email) {
                        $user = $existingUsersByEmail->get($email);
                    }

                    if (!$user) {
                        $user = User::create([
                            'name' => $row['nama'],
                            'email' => $email,
                            'password' => $getPasswordHash($row['password'] ?? null),
                            'role' => 'siswa',
                            'jenis_kelamin' => $gender,
                            'no_hp' => $row['nomor_hp'] ?? null,
                        ]);
                        if ($email) {
                            $existingUsersByEmail->put($email, $user);
                        }
                    } else {
                        $user->update([
                            'name' => $row['nama'],
                            'jenis_kelamin' => $gender ?? $user->jenis_kelamin,
                            'no_hp' => $row['nomor_hp'] ?? $user->no_hp,
                        ]);
                        if (!empty($row['password'])) {
                            $user->update([
                                'password' => $getPasswordHash($row['password']),
                            ]);
                        }
                    }

                    $newStudent = Student::create([
                        'user_id' => $user->id,
                        'nis' => $nis,
                        'class_id' => $classId,
                        'no_hp' => $row['nomor_hp'] ?? null,
                    ]);
                    if ($nis) {
                        $existingStudents->put($nis, $newStudent);
                    }
                }
            }
        });
    }
}
