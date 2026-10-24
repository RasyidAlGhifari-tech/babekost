<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // `role` dan `is_active` sengaja tidak di-fillable: registrasi publik
    // tidak boleh menentukan role atau status akun sendiri.
    // Default di database: role = 'penyewa', is_active = true.
    protected $fillable = ['name', 'email', 'phone', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Nomor HP bersifat opsional (bukan untuk login).
    // Jika diisi, disimpan dalam format 08xxxxxxxxxx.
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => filled($value) ? self::normalizePhone($value) : null,
        );
    }

    public static function normalizePhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value ?? '');

        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits;
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTenant(): bool
    {
        return $this->role === 'penyewa';
    }
}