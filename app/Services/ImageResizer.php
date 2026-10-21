<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageResizer
{
    /**
     * Simpan foto dengan ukuran otomatis diperkecil (bagian 6 & Hari 6).
     * Dipakai lewat FileUpload::saveUploadedFileUsing() di Filament, jadi
     * resize terjadi SEBELUM file sampai ke disk akhir (bukan upload asli
     * lalu resize belakangan).
     *
     * @param  string  $disk  'public' untuk foto kamar/lokasi/agenda yang
     *                        memang publik. Bukti transfer TIDAK lewat
     *                        service ini — itu pakai disk privat tanpa
     *                        resize (bagian 7 Stack).
     */
    public static function storeResized(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxWidth = 1280,
    ): string {
        $filename = $directory . '/' . Str::uuid() . '.webp';

        $encoded = Image::read($file->getRealPath())
            ->scaleDown(width: $maxWidth)
            ->toWebp(quality: 80);

        Storage::disk($disk)->put($filename, (string) $encoded);

        return $filename;
    }
}
