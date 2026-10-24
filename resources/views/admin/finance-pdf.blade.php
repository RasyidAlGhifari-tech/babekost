<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #141414; }
        h2 { margin: 0 0 4px; } p { margin: 0 0 12px; color: #5B5B57; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #E3DFD3; padding: 6px 8px; text-align: left; }
        th { background: #F7EFDC; } td.n { text-align: right; }
        tr.total td { font-weight: bold; background: #F8F7F4; }
    </style>
</head>
<body>
    <h2>Laporan Keuangan Babekost</h2>
    <p>Periode {{ $month->translatedFormat('F Y') }}</p>
    <table>
        <tr><th>Bangunan</th><th>Pemasukan</th><th>Pengeluaran</th><th>Laba</th></tr>
        @foreach ($rows as $r)
            <tr class="{{ $r[0] === 'TOTAL' ? 'total' : '' }}">
                <td>{{ $r[0] }}</td>
                @foreach ([1, 2, 3] as $i)
                    <td class="n">Rp {{ number_format($r[$i], 0, ',', '.') }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>