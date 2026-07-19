<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'siswa'])->default('siswa')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('no_hp')->nullable()->after('is_active');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'no_hp', 'jenis_kelamin']);
        });
    }
};
