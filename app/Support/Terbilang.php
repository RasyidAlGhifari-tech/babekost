<?php

namespace App\Support;

class Terbilang
{
    private const SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    /** 300000 -> "Tiga ratus ribu rupiah" */
    public static function rupiah(int $n): string
    {
        $text = $n === 0 ? 'nol' : trim(preg_replace('/\s+/', ' ', self::convert($n)));

        return ucfirst($text) . ' rupiah';
    }

    private static function convert(int $n): string
    {
        if ($n < 12)         return self::SATUAN[$n];
        if ($n < 20)         return self::SATUAN[$n - 10] . ' belas';
        if ($n < 100)        return self::SATUAN[intdiv($n, 10)] . ' puluh ' . self::convert($n % 10);
        if ($n < 200)        return 'seratus ' . self::convert($n - 100);
        if ($n < 1000)       return self::SATUAN[intdiv($n, 100)] . ' ratus ' . self::convert($n % 100);
        if ($n < 2000)       return 'seribu ' . self::convert($n - 1000);
        if ($n < 1000000)    return self::convert(intdiv($n, 1000)) . ' ribu ' . self::convert($n % 1000);
        if ($n < 1000000000) return self::convert(intdiv($n, 1000000)) . ' juta ' . self::convert($n % 1000000);

        return self::convert(intdiv($n, 1000000000)) . ' miliar ' . self::convert($n % 1000000000);
    }
}