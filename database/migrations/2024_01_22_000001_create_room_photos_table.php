<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto kamar (Hari 6). Tidak ada di draft tabel bagian 10 dokumen
     * perencanaan (yang ada hanya kolom data kamar, tanpa foto) — tabel
     * ini ditambahkan karena kartu kamar di landing page (bagian 9)
     * butuh foto, dan dokumen sendiri meminta "upload foto otomatis
     * diperkecil" untuk kamar di Hari 6. Mengikuti pola yang sama
     * seperti agenda_media: satu kamar bisa punya banyak foto.
     */
    public function up(): void
    {
        Schema::create('room_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_photos');
    }
};
