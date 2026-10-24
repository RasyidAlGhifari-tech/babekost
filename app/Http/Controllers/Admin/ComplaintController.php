<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['diproses', 'selesai']) ? $request->query('tab') : 'baru';

        $counts = Complaint::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        $complaints = Complaint::with('room', 'tenant.user')->where('status', $tab)->latest()->get();

        return view('admin.complaints', compact('tab', 'counts', 'complaints'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $status = $request->validate(['status' => ['required', 'in:diproses,selesai']])['status'];

        $complaint->update([
            'status'      => $status,
            'resolved_at' => $status === 'selesai' ? now() : null,
        ]);

        return back()->with('status', 'Status keluhan diperbarui.');
    }
}