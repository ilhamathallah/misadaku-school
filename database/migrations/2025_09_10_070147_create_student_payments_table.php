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
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            // $table->foreignId('discount_id')->nullable()->constrained('student_discounts')->nullOnDelete();
            $table->unsignedBigInteger('discount_id')->nullable();
            // $table->foreignId('bill_id')->nullable()->constrained()->cascadeOnDelete();
            $table->json('bill_ids')->nullable(); // untuk simpan banyak id tagihan
            // $table->date('payment_date');
            $table->enum('method', ['Transfer', 'Cash']);
            $table->integer('total_amount')->default(0);
            $table->integer('paid_amount')->required();
            $table->text('note')->nullable();
            $table->text('reason')->nullable();
            $table->enum('sum', ['lebih', 'kurang', 'lunas']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
