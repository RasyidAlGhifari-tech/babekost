<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Support\Notifier;
use Illuminate\Console\Command;

class SyncBookings extends Command
{
    protected $signature = 'bookings:sync {--dry-run : Tampilkan hasil tanpa mengubah data}';

    protected $description = 'Selesaikan booking yang habis, batalkan yang tidak dibayar, kirim pengingat perpanjangan';

    public function handle(): int
    {
        $dry   = (bool) $this->option('dry-run');
        $today = today();
        $hours = (int) config('babekost.payment_deadline_hours', 48);
        $days  = (int) config('babekost.renewal_reminder_days', 7);

        // 1. aktif -> selesai
        $finished = Booking::with(['room', 'tenant.user'])
            ->where('status', 'aktif')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($finished as $b) {
            if ($dry) continue;

            $b->forceFill(['status' => 'selesai'])->save();

            Notifier::toAdmins(
                'Kamar perlu dibersihkan',
                "Masa sewa Kamar {$b->room->number} sudah berakhir. Kamar siap dibersihkan.",
                route('admin.rooms', [], false),
                'sparkles'
            );
        }

        // 2. menunggu_pembayaran -> dibatalkan
        $unpaid = Booking::with(['room', 'tenant.user'])
            ->where('status', 'menunggu_pembayaran')
            ->where('created_at', '<', now()->subHours($hours))
            ->whereDoesntHave('payments', fn ($q) => $q->whereIn('status', ['menunggu_verifikasi', 'terverifikasi']))
            ->get();

        foreach ($unpaid as $b) {
            if ($dry) continue;

            $b->forceFill([
                'status'              => 'dibatalkan',
                'cancelled_at'        => now(),
                'cancellation_reason' => "Tidak dibayar dalam {$hours} jam (otomatis).",
            ])->save();

            Notifier::toUser(
                $b->tenant?->user,
                'Booking dibatalkan',
                "Booking Kamar {$b->room->number} dibatalkan karena belum dibayar dalam {$hours} jam.",
                route('tenant.booking', ['tab' => 'saya'], false),
                'x-circle'
            );
        }

        // 3. Pengingat perpanjangan (satu kali per hari per penyewa)
        $ending = Booking::with(['room', 'tenant.user'])
            ->where('status', 'aktif')
            ->whereDate('end_date', $today->copy()->addDays($days))
            ->get();

        $reminded = 0;
        foreach ($ending as $b) {
            $user = $b->tenant?->user;
            if ($dry || ! $user) continue;

            $already = $user->notifications()
                ->where('data->title', 'Masa sewa hampir habis')
                ->whereDate('created_at', $today)
                ->exists();
            if ($already) continue;

            Notifier::toUser(
                $user,
                'Masa sewa hampir habis',
                "Masa sewa Kamar {$b->room->number} berakhir {$b->end_date->translatedFormat('j M Y')}. Mohon konfirmasi ke admin jika tidak memperpanjang.",
                route('tenant.booking', ['tab' => 'saya'], false),
                'clock'
            );
            $reminded++;
        }

        $this->info($dry ? 'DRY RUN, tidak ada data yang diubah.' : 'Selesai.');
        $this->table(['Aturan', 'Jumlah'], [
            ['aktif -> selesai', $finished->count()],
            ["menunggu_pembayaran -> dibatalkan (> {$hours} jam)", $unpaid->count()],
            ["pengingat H-{$days}", $dry ? $ending->count() : $reminded],
        ]);

        return self::SUCCESS;
    }
}