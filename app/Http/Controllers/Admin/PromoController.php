<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.promos', [
            'tab'     => $request->query('tab') === 'events' ? 'events' : 'promo',
            'promos'  => Promo::latest()->get(),
            'agendas' => Agenda::orderByDesc('agenda_date')->get(),
        ]);
    }

    public function storePromo(Request $request)
    {
        Promo::create($request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ]) + ['is_active' => true]);

        return back()->with('status', 'Promo ditambahkan.');
    }

    public function togglePromo(Promo $promo)
    {
        $promo->update(['is_active' => ! $promo->is_active]);

        return back();
    }

    public function destroyPromo(Promo $promo)
    {
        $promo->delete();

        return back()->with('status', 'Promo dihapus.');
    }

    public function storeAgenda(Request $request)
    {
        Agenda::create($request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'agenda_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]) + ['status' => 'rencana']);

        return redirect()->route('admin.promos', ['tab' => 'events'])->with('status', 'Agenda ditambahkan.');
    }

    public function doneAgenda(Agenda $agenda)
    {
        $agenda->update(['status' => 'selesai']);

        return back();
    }

    public function destroyAgenda(Agenda $agenda)
    {
        $agenda->delete();

        return back()->with('status', 'Agenda dihapus.');
    }
}