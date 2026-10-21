<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            // Seeder data lokasi/kamar contoh ditambahkan di Hari 5-6,
            // setelah CRUD bangunan & kamar ada.
        ]);
    }
}
