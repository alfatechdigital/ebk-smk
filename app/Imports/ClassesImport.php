<?php

namespace App\Imports;

use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Institute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClassesImport implements ToCollection, WithHeadingRow
{
    protected $forceUpdate;

    public function __construct($forceUpdate = false)
    {
        $this->forceUpdate = $forceUpdate;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use ($rows) {
            // Load all teachers with their user names
            $teachers = Teacher::with('user')->get()->keyBy(function($t) {
                return strtolower(trim($t->user->name));
            });

            // Load all existing classes
            $existingClasses = SchoolClass::all()->keyBy(function($c) {
                return strtolower(trim($c->name));
            });

            $instituteId = Institute::first()->id ?? null;

            // 1. Pre-validate for duplicate Class names in spreadsheet
            $classNamesList = [];
            foreach ($rows as $row) {
                if (empty($row['kelas'])) {
                    continue;
                }
                $className = trim($row['kelas']);
                $lowerName = strtolower($className);
                if (in_array($lowerName, $classNamesList)) {
                    throw new \Exception("Ditemukan duplikasi Kelas '{$className}' di dalam file Excel yang Anda unggah.");
                }
                $classNamesList[] = $lowerName;

                // Check duplicate in database (only if not forcing update)
                if (!$this->forceUpdate) {
                    if ($existingClasses->has($lowerName)) {
                        throw new \Exception("Kelas '{$className}' sudah terdaftar di database. Proses import dibatalkan.");
                    }
                }
            }

            // 2. Perform inserts/updates
            foreach ($rows as $row) {
                if (empty($row['kelas'])) {
                    continue;
                }

                $className = trim($row['kelas']);
                $lowerName = strtolower($className);
                $waliKelas = !empty($row['wali_kelas']) ? trim($row['wali_kelas']) : null;
                $guruBkName = !empty($row['guru_bk']) ? trim($row['guru_bk']) : null;

                $teacherId = null;
                if ($guruBkName) {
                    $teacher = $teachers->get(strtolower($guruBkName));
                    $teacherId = $teacher ? $teacher->id : null;
                }

                $existingClass = $existingClasses->get($lowerName);

                if ($existingClass) {
                    // Update
                    $existingClass->update([
                        'wali_kelas' => $waliKelas ?? $existingClass->wali_kelas,
                        'teacher_id' => $teacherId ?? $existingClass->teacher_id,
                    ]);
                } else {
                    // Create
                    SchoolClass::create([
                        'name' => $className,
                        'wali_kelas' => $waliKelas,
                        'teacher_id' => $teacherId,
                        'institute_id' => $instituteId,
                    ]);
                }
            }
        });
    }
}
