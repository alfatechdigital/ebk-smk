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
            $table->integer('media_expiry_days')->nullable()->default(null);
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->timestamp('media_opened_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('media_expiry_days');
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropColumn('media_opened_at');
        });
    }
};
