<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pengeluaran;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluarans = Pengeluaran::orderBy('tanggal', 'desc')->get();
        $totalPengeluaran = $pengeluarans->sum('nominal');
        
        return view('admin.pengeluaran.index', compact('pengeluarans', 'totalPengeluaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|numeric',
        ]);

        $pengeluaran = new Pengeluaran();
        $pengeluaran->tanggal = $validated['tanggal'];
        $pengeluaran->kategori = $validated['kategori'];
        $pengeluaran->deskripsi = $validated['deskripsi'] ?? '-';
        $pengeluaran->nominal = $validated['nominal'];
        $pengeluaran->pengguna = auth()->user()->name ?? 'Admin';
        $pengeluaran->save();

        return redirect()->route('admin.pengeluaran.index')->with('success', 'Pengeluaran berhasil ditambahkan.');
    }
}
