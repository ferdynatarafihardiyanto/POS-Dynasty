<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Http\Requests\StoreCafeTableRequest;
use App\Http\Requests\UpdateCafeTableRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CafeTableController extends Controller
{
    public function index()
    {
        $mejas = CafeTable::orderByRaw('CAST(table_number AS UNSIGNED) ASC, table_number ASC')->paginate(10);
        return view('admin.meja.index', compact('mejas'));
    }

    public function create()
    {
        return view('admin.meja.create');
    }

    public function store(StoreCafeTableRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'active';
        $data['qr_token'] = Str::random(10);
        CafeTable::create($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(CafeTable $meja)
    {
        return view('admin.meja.edit', compact('meja'));
    }

    public function update(UpdateCafeTableRequest $request, CafeTable $meja)
    {
        $data = $request->validated();
        if (empty($data['status'])) {
            unset($data['status']);
        }
        $meja->update($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(CafeTable $meja)
    {
        if ($meja->pesanan()->count() > 0) {
            return redirect()->route('admin.meja.index')->with('error', 'Meja tidak dapat dihapus karena sudah memiliki riwayat pesanan. Pertimbangkan untuk menonaktifkannya saja.');
        }
        $meja->delete();
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil dihapus.');
    }
}
