<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn(['npsn', 'email', 'logo_path', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('npsn')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('tahun_ajaran')->default('2024/2025');
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
        });
    }
};
