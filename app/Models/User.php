<?php

namespace App\Models;

// NOTE: ini adalah pengganti app/Models/User.php bawaan Breeze.
// Kalau kamu sudah sempat mengubah User.php sendiri, cukup gabungkan
// (merge) bagian role/soft-delete di bawah, jangan timpa begitu saja.

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPenyewa(): bool
    {
        return $this->role === 'penyewa';
    }
}
