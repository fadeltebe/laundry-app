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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // relasi ke customer
            $table->string('description')->nullable(); // catatan tambahan

            $table->dateTime('received_at')->nullable(); // kapan diterima
            $table->dateTime('completed_at')->nullable(); // kapan selesai
            $table->enum('status', ['Diterima', 'Diproses', 'Selesai', 'Diambil'])->default('Diterima');

            $table->integer('amount'); // total tagihan
            $table->string('payment_method')->nullable(); // contoh: 'Tunai', 'Transfer', 'QRIS'
            $table->dateTime('paid_at')->nullable(); // kapan dibayar
            $table->enum('payment_status', ['Belum Lunas', 'Lunas'])->default('Belum Lunas');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'received_at',
                'completed_at',
                'status',
                'payment_method',
                'paid_at',
                'payment_status',
            ]);
        });
    }
};
