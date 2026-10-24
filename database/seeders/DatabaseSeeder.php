<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CatalogSeeder::class,
            BabeKostDataSeeder::class,
        ]);

        // Penyewa uji: penyewa@babekost.test / password123
        $user = User::create([
            'name'     => 'Penyewa Uji',
            'email'    => 'penyewa@babekost.test',
            'phone'    => '081200000001',
            'password' => 'password123',
        ]);
        $tenant = $user->tenant()->create(['phone' => $user->phone]);

        $room = Room::orderBy('id')->first();

        Booking::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'package'        => 'yearly',
            'start_date'     => today()->subDays(30),
            'end_date'       => today()->addDays(120),
            'total_price'    => $room->price_yearly,
            'deposit_amount' => 0,
            'status'         => 'aktif',
        ]);
    }
}