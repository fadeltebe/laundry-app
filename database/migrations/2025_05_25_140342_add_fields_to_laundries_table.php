<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->string('nama_owner')->default('Owner Laundry');
            $table->string('kontak_owner')->nullable();
            $table->string('logo')->default('logo.png');
            $table->string('slogan')->default('Laundry Bersih, Wangi, dan Hemat');
            $table->string('alamat')->nullable();
            $table->string('kontak')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn([
                'nama_owner',
                'kontak_owner',
                'logo',
                'slogan',
                'alamat',
                'kontak',
            ]);
        });
    }
};
