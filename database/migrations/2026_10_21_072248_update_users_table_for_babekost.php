<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * role: admin (1 akun via seeder) / penyewa (daftar sendiri).
     * Email unik yang sadar soft delete lewat kolom generated email_sort.
     * Butuh MySQL/MariaDB.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'penyewa'])->default('penyewa')->after('password');
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('email_sort')
                ->storedAs('IF(`deleted_at` IS NULL, 0, TO_SECONDS(`deleted_at`))')
                ->after('deleted_at');
            $table->unique(['email', 'email_sort'], 'users_email_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_active_unique');
            $table->dropColumn('email_sort');
            $table->dropSoftDeletes();
            $table->dropColumn('role');
            $table->unique('email', 'users_email_unique');
        });
    }
};