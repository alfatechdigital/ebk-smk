<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Institute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TeachersImport implements ToCollection, WithHeadingRow
{
    protected $forceUpdate;

    public function __construct($forceUpdate = false)
    {
        $this->forceUpdate = $forceUpdate;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use ($rows) {
            $institute = Institute::first();
            $instituteId = $institute ? $institute->id : null;

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

            // 1. Pre-validate for duplicate NIP and Email values
            $nipList = [];
            $emailList = [];
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }
                $nip = !empty($row['nip']) ? trim($row['nip']) : null;
                $email = !empty($row['email']) ? trim($row['email']) : null;
                
                if (!$email) {
                    throw new \Exception("Kolom Email wajib diisi untuk Guru '" . $row['nama'] . "'.");
                }

                if ($nip) {
                    if (in_array($nip, $nipList)) {
                        throw new \Exception("Ditemukan duplikasi NIP '{$nip}' di dalam file Excel yang Anda unggah.");
                    }
                    $nipList[] = $nip;
                }
                if ($email) {
                    if (in_array($email, $emailList)) {
                        throw new \Exception("Ditemukan duplikasi Email '{$email}' di dalam file Excel yang Anda unggah.");
                    }
                    $emailList[] = $email;
                }
            }

            // Batch database duplicate check (only if not forcing update)
            if (!$this->forceUpdate) {
                if (!empty($nipList)) {
                    $duplicateNip = Teacher::whereIn('nip', $nipList)->pluck('nip')->first();
                    if ($duplicateNip) {
                        throw new \Exception("NIP '{$duplicateNip}' sudah terdaftar di database. Proses import dibatalkan.");
                    }
                }
                if (!empty($emailList)) {
                    $duplicateEmail = User::whereIn('email', $emailList)->pluck('email')->first();
                    if ($duplicateEmail) {
                        throw new \Exception("Email '{$duplicateEmail}' sudah terdaftar di database. Proses import dibatalkan.");
                    }
                }
            }

            // Batch load existing teachers and users to prevent N+1 query bottlenecks in loop
            $existingTeachers = Teacher::whereIn('nip', $nipList)->with('user')->get()->keyBy('nip');
            $existingUsersByEmail = User::whereIn('email', $emailList)->get()->keyBy('email');

            // 2. Perform inserts/updates since validation passed
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }

                $nip = !empty($row['nip']) ? trim($row['nip']) : null;
                $email = !empty($row['email']) ? trim($row['email']) : null;
                $spesialisasi = !empty($row['spesialisasi']) ? trim($row['spesialisasi']) : null;
                $noHp = !empty($row['no_hp']) ? trim($row['no_hp']) : null;

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

                // Check for existing records
                $existingTeacher = $nip ? ($existingTeachers[$nip] ?? null) : null;
                $existingUser = $email ? ($existingUsersByEmail[$email] ?? null) : null;

                if ($this->forceUpdate) {
                    if ($existingTeacher) {
                        // Update existing teacher's user and teacher info
                        $user = $existingTeacher->user;
                        if ($user) {
                            $user->update([
                                'name' => $row['nama'],
                                'email' => $email,
                                'no_hp' => $noHp,
                                'jenis_kelamin' => $gender,
                            ]);
                            if (!empty($row['password'])) {
                                $user->update(['password' => $getPasswordHash($row['password'])]);
                            }
                        }
                        $existingTeacher->update([
                            'no_whatsapp' => $noHp,
                            'spesialisasi' => $spesialisasi,
                        ]);
                        continue;
                    } elseif ($existingUser) {
                        // Update existing user of role guru or change to guru
                        $existingUser->update([
                            'name' => $row['nama'],
                            'role' => 'guru',
                            'no_hp' => $noHp,
                            'jenis_kelamin' => $gender,
                        ]);
                        if (!empty($row['password'])) {
                            $existingUser->update(['password' => $getPasswordHash($row['password'])]);
                        }

                        Teacher::updateOrCreate(
                            ['user_id' => $existingUser->id],
                            [
                                'nip' => $nip,
                                'institute_id' => $instituteId,
                                'no_whatsapp' => $noHp,
                                'spesialisasi' => $spesialisasi,
                            ]
                        );
                        continue;
                    }
                }

                // Create new user and teacher
                $user = User::create([
                    'name' => $row['nama'],
                    'email' => $email,
                    'password' => $getPasswordHash($row['password'] ?? null),
                    'role' => 'guru',
                    'jenis_kelamin' => $gender,
                    'no_hp' => $noHp,
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $nip,
                    'institute_id' => $instituteId,
                    'no_whatsapp' => $noHp,
                    'spesialisasi' => $spesialisasi,
                ]);
            }
        });
    }
}
