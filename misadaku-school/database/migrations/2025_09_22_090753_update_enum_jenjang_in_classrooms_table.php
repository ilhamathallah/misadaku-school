<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('classrooms', function (Blueprint $table) {

        // });
        DB::statement("ALTER TABLE classrooms MODIFY jenjang ENUM('SD', 'MI', 'SMP', 'SMA') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('classrooms', function (Blueprint $table) {
        //     //
        // });
        DB::statement("ALTER TABLE classrooms MODIFY jenjang ENUM('SD', 'SMP', 'SMA') NOT NULL");
    }
};
