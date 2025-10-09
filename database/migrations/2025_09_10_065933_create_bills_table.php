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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->json('category_ids')->constrained('finance_categories')->onDelete('cascade');
            $table->string('nama_tagihan'); // Contoh: SPP Juli + Uang Seragam
            $table->integer('amount'); // total tagihan (dari kategori)
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', ['Belum Lunas', 'Kurang', 'Lunas'])->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
