<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tata tertib: SATU baris saja, berlaku untuk semua kos (bagian 6
     * & 10). Tidak butuh foreign key ke lokasi — teksnya sama untuk
     * semua bangunan. Penyewa harus menyetujui ini saat booking
     * (house_rules_agreed_at di tabel rentals, Hari 10).
     */
    public function up(): void
    {
        Schema::create('house_rules', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('house_rules');
    }
};
