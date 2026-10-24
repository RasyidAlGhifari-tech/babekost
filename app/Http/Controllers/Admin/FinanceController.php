<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanExport;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Expense;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pengeluaran', 'laba']) ? $request->query('tab') : 'verifikasi';
        $data = ['tab' => $tab];

        if ($tab === 'verifikasi') {
            $status = in_array($request->query('status'), ['terverifikasi', 'ditolak']) ? $request->query('status') : 'menunggu_verifikasi';
            $data += [
                'status'   => $status,
                'counts'   => Payment::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
                'payments' => Payment::with('booking.room', 'booking.tenant.user')->where('status', $status)->latest()->get(),
            ];
        }

        if ($tab === 'pengeluaran') {
            $bid   = (int) $request->query('building');
            $month = today()->startOfMonth();
            $base  = Expense::with('building')->whereYear('expense_date', $month->year)
                ->whereMonth('expense_date', $month->month)
                ->when($bid, fn ($q) => $q->where('building_id', $bid));

            $data += [
                'bid'       => $bid,
                'buildings' => Building::orderBy('name')->get(),
                'total'     => (clone $base)->sum('amount'),
                'expenses'  => $base->latest('expense_date')->get(),
            ];
        }

        if ($tab === 'laba') {
            $month = $this->month($request);
            $data += ['month' => $month] + $this->report($month);
        }

        return view('admin.finance', $data);
    }

    // ---------- Verifikasi ----------
    public function approve(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status'           => 'terverifikasi',
                'verified_by'      => auth()->user()->admin?->id,
                'verified_at'      => now(),
                'receipt_number'   => 'KW-' . now()->format('Ymd') . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT),
                'rejection_reason' => null,
            ]);

            $booking = $payment->booking;
            $paid = $booking->payments()->where('status', 'terverifikasi')->sum('amount');
            $booking->update(['status' => $paid >= $booking->total_price ? 'aktif' : 'booking']);
        });

        return back()->with('status', 'Pembayaran disetujui.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);

        $payment->update([
            'status'           => 'ditolak',
            'verified_by'      => auth()->user()->admin?->id,
            'verified_at'      => now(),
            'rejection_reason' => $data['reason'] ?? 'Bukti transfer tidak sesuai.',
        ]);

        return back()->with('status', 'Pembayaran ditolak.');
    }

    public function proof(Payment $payment)
    {
        abort_unless(Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }

    // ---------- Pengeluaran ----------
    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'building_id'  => ['required', 'exists:buildings,id'],
            'category'     => ['required', 'in:air,listrik,perawatan,lainnya'],
            'amount'       => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:255'],
        ]);

        Expense::create($data);

        return redirect()->route('admin.finance', ['tab' => 'pengeluaran'])->with('status', 'Pengeluaran ditambahkan.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', 'Pengeluaran dihapus.');
    }

    // ---------- Laba & ekspor ----------
    public function export(Request $request, string $type)
    {
        $month = $this->month($request);
        $r = $this->report($month);

        $rows = collect($r['perBuilding'])->map(fn ($b) => [
            $b['name'], $b['income'], $b['expense'], $b['income'] - $b['expense'],
        ])->push(['TOTAL', $r['income'], $r['expense'], $r['profit']])->all();

        $file = 'laporan-keuangan-' . $month->format('Y-m');

        if ($type === 'excel') {
            return Excel::download(new LaporanExport($rows), $file . '.xlsx');
        }

        return Pdf::loadView('admin.finance-pdf', ['month' => $month, 'rows' => $rows])
            ->download($file . '.pdf');
    }

    private function month(Request $request): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $request->query('month', today()->format('Y-m')) . '-01')->startOfMonth();
        } catch (\Throwable) {
            return today()->startOfMonth();
        }
    }

    private function income(Carbon $m)
    {
        return Payment::where('status', 'terverifikasi')
            ->whereYear('paid_at', $m->year)->whereMonth('paid_at', $m->month);
    }

    private function report(Carbon $month): array
    {
        $income  = (int) $this->income($month)->sum('amount');
        $expense = (int) Expense::whereYear('expense_date', $month->year)->whereMonth('expense_date', $month->month)->sum('amount');

        // Laba 6 bulan yang berakhir di bulan terpilih.
        $chart = collect(range(5, 0))->map(function ($i) use ($month) {
            $m = $month->copy()->subMonths($i);
            $in  = (int) $this->income($m)->sum('amount');
            $out = (int) Expense::whereYear('expense_date', $m->year)->whereMonth('expense_date', $m->month)->sum('amount');
            return ['label' => $m->translatedFormat('M'), 'profit' => $in - $out];
        });

        $incomeBy = $this->income($month)->with('booking.room')->get()
            ->groupBy(fn ($p) => $p->booking->room->building_id)->map->sum('amount');
        $expenseBy = Expense::whereYear('expense_date', $month->year)->whereMonth('expense_date', $month->month)
            ->get()->groupBy('building_id')->map->sum('amount');

        $perBuilding = Building::orderBy('name')->get()->map(fn ($b) => [
            'name'    => $b->name,
            'income'  => (int) ($incomeBy[$b->id] ?? 0),
            'expense' => (int) ($expenseBy[$b->id] ?? 0),
        ])->all();

        return compact('income', 'expense', 'chart', 'perBuilding') + ['profit' => $income - $expense];
    }
}