<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = Satuan::latest()->paginate(10);
        return view('admin.satuan.index', compact('satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50|unique:satuans,nama',
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'nullable|boolean'
        ], [
            'nama.unique' => 'Nama satuan sudah ada di sistem.',
            'nama.required' => 'Nama satuan wajib diisi.'
        ]);

        $validated['aktif'] = $request->boolean('aktif');

        Satuan::create($validated);
        return redirect()->route('admin.satuan.index')->with('success', 'Satuan baru berhasil ditambahkan');
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('satuans', 'nama')->ignore($satuan->id)],
            'keterangan' => 'nullable|string|max:255',
            'aktif' => 'nullable|boolean'
        ], [
            'nama.unique' => 'Nama satuan sudah ada di sistem.',
            'nama.required' => 'Nama satuan wajib diisi.'
        ]);

        $validated['aktif'] = $request->boolean('aktif');

        $satuan->update($validated);
        return redirect()->route('admin.satuan.index')->with('success', 'Satuan berhasil diperbarui');
    }

    public function destroy(Satuan $satuan)
    {
        try {
            $satuan->delete();
            return redirect()->route('admin.satuan.index')->with('success', 'Satuan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.satuan.index')->with('error', 'Gagal menghapus satuan.');
        }
    }
}
