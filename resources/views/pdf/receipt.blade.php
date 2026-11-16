@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kwitansi {{ $payment->receipt_number }}</title>
<style>
    @page { margin: 20px 24px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1c1b17; }
    table { width: 100%; border-collapse: collapse; }
    .frame { border: 1.5px solid #7a5a12; padding: 12px 18px; }
    .head td { vertical-align: middle; }
    .brand { font-size: 14px; font-weight: bold; }
    .muted { color: #6b6a63; }
    h1 { margin: 0; text-align: right; font-size: 17px; letter-spacing: 3px; color: #7a5a12; }
    .no { text-align: right; font-size: 10px; margin-top: 2px; }
    hr { border: 0; border-top: 1px solid #e3dfd3; margin: 8px 0; }
    .rows td { padding: 3px 0; vertical-align: top; }
    .rows td.l { width: 26%; color: #6b6a63; }
    .amount { border: 1px solid #7a5a12; background: #f8f1df; padding: 7px 12px; font-size: 15px; font-weight: bold; }
    .sum td { padding: 2px 0; }
    .sum td.r { text-align: right; }
    .foot { margin-top: 8px; font-size: 8px; color: #6b6a63; }
</style>
</head>
<body>
<div class="frame">

    <table class="head">
        <tr>
            <td style="width: 55%;">
                <table>
                    <tr>
                        @if ($logo)
                            <td style="width: 46px;"><img src="{{ $logo }}" style="height: 40px;"></td>
                        @endif
                        <td>
                            <div class="brand">{{ config('babekost.name') }}</div>
                            <div class="muted">{{ $building->name }}</div>
                            <div class="muted">{{ $building->address }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <h1>KWITANSI</h1>
                <div class="no">No. {{ $payment->receipt_number }}</div>
            </td>
        </tr>
    </table>

    <hr>

    <table class="rows">
        <tr>
            <td class="l">Telah terima dari</td>
            <td>: <b>{{ $payer->name }}</b></td>
        </tr>
        <tr>
            <td class="l">Untuk pembayaran</td>
            <td>: {{ $payment->type === 'dp' ? 'DP' : 'Pelunasan' }} sewa Kamar {{ $room->number }}, {{ $building->name }},
                paket {{ $package }}, periode {{ $booking->start_date->translatedFormat('j M Y') }}
                sampai {{ $booking->end_date->translatedFormat('j M Y') }}</td>
        </tr>
        <tr>
            <td class="l">Terbilang</td>
            <td>: <i>{{ $words }}</i></td>
        </tr>
        <tr>
            <td class="l">Tanggal bayar</td>
            <td>: {{ $payment->paid_at->translatedFormat('j F Y') }}</td>
        </tr>
        <tr>
            <td class="l">Metode</td>
            <td>: Transfer {{ $bank['name'] }} {{ $bank['number'] }}</td>
        </tr>
    </table>

    <div class="amount" style="margin-top: 8px;">{{ $rp($payment->amount) }}</div>

    <table class="sum" style="margin-top: 8px; width: 55%;">
        <tr><td class="muted">Total tagihan</td><td class="r">{{ $rp($booking->total_price) }}</td></tr>
        <tr><td class="muted">Dibayar sampai kwitansi ini</td><td class="r">{{ $rp($paidToDate) }}</td></tr>
        <tr><td><b>Sisa tagihan</b></td><td class="r"><b>{{ $rp($remaining) }}</b></td></tr>
    </table>

    <div class="foot">
        Kwitansi ini diterbitkan otomatis oleh sistem {{ config('babekost.name') }} setelah pembayaran diverifikasi
        ({{ now()->translatedFormat('j F Y H:i') }}).
    </div>
</div>
</body>
</html>