<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /** Satu akun admin (pemilik). Kredensial dari .env. Aman dijalankan ulang. */
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'pemilik@babekost.com');
        $password = env('ADMIN_PASSWORD', 'babekost-admin-passphrase');
        $phone    = env('ADMIN_PHONE', '081222223215');

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name'       => 'Pemilik Babekost',
            'phone'      => $phone,
            'password'   => $password,
            'role'       => 'admin',
            'is_active'  => true,
            'deleted_at' => null,
        ])->save();

        $admin = Admin::withTrashed()->firstOrNew(['user_id' => $user->id]);
        $admin->forceFill(['phone' => $user->phone, 'deleted_at' => null])->save();

        $this->command?->info("Akun admin siap: {$email}");
    }
}