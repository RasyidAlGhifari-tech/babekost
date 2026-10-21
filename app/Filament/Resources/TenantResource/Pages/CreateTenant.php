<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * Jalur admin untuk input penyewa manual (bagian 4.5: penghuni
     * lama / walk-in yang belum punya akun). User + Tenant dibuat
     * bersamaan dalam satu transaksi, sama seperti registrasi mandiri
     * di Hari 4 — supaya konsisten, tidak ada dua cara berbeda yang
     * bisa menghasilkan User tanpa Tenant atau sebaliknya.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'role' => 'penyewa',
                'email_verified_at' => now(),
            ]);

            return Tenant::create([
                'user_id' => $user->id,
                'phone' => $data['phone'],
                'emergency_contact' => $data['emergency_contact'],
                'campus_or_job' => $data['campus_or_job'] ?? null,
            ]);
        });
    }
}
