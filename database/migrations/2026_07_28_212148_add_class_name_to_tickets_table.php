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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('class_name')->nullable()->after('class_id');
        });

        // Populate class_name for existing tickets
        try {
            $tickets = DB::table('tickets')->get();
            foreach ($tickets as $t) {
                if ($t->class_id) {
                    $className = DB::table('classes')->where('id', $t->class_id)->value('name');
                    if ($className) {
                        DB::table('tickets')->where('id', $t->id)->update(['class_name' => $className]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore if tables do not exist or query fails during fresh seeding
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });
    }
};
