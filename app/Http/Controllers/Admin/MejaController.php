<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CafeTable;
use Illuminate\Support\Str;

class MejaController extends Controller
{
    public function daftarMeja(Request $request)
    {
        $query = CafeTable::query();

        if ($request->has('cari')) {
            $cari = $request->cari;
            $query->where(function($q) use ($cari) {
                $q->where('table_number', 'like', "%{$cari}%")
                  ->orWhere('name', 'like', "%{$cari}%")
                  ->orWhere('qr_token', 'like', "%{$cari}%");
            });
        }

        if ($request->has('aktif')) {
            $status = filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN) ? 'active' : 'inactive';
            $query->where('status', $status);
        }

        $meja = $query->get()->map(function($m) {
            return [
                'id' => $m->id,
                'nomor_meja' => $m->table_number,
                'nama_meja' => $m->name,
                'kode_meja' => $m->qr_token,
                'aktif' => $m->status === 'active'
            ];
        });

        return response()->json([
            'message' => 'Data meja berhasil diambil',
            'data' => $meja
        ]);
    }

    public function tambahMeja(Request $request)
    {
        $request->validate([
            'nomor_meja' => 'required|string|unique:cafe_tables,table_number',
            'nama_meja' => 'required|string|max:100'
        ], [
            'nomor_meja.unique' => 'Nomor meja sudah digunakan'
        ]);

        $kodeMeja = 'meja-' . strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->nomor_meja)));

        $meja = CafeTable::create([
            'table_number' => $request->nomor_meja,
            'name' => $request->nama_meja,
            'qr_token' => $kodeMeja,
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Meja berhasil ditambahkan',
            'data' => [
                'id' => $meja->id,
                'nomor_meja' => $meja->table_number,
                'nama_meja' => $meja->name,
                'kode_meja' => $meja->qr_token,
                'aktif' => $meja->status === 'active'
            ]
        ], 201);
    }

    public function detailMeja($id)
    {
        $meja = CafeTable::find($id);

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Data meja berhasil diambil',
            'data' => [
                'id' => $meja->id,
                'nomor_meja' => $meja->table_number,
                'nama_meja' => $meja->name,
                'kode_meja' => $meja->qr_token,
                'aktif' => $meja->status === 'active'
            ]
        ]);
    }

    public function ubahMeja(Request $request, $id)
    {
        $meja = CafeTable::find($id);

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $request->validate([
            'nomor_meja' => 'required|string|unique:cafe_tables,table_number,' . $id,
            'nama_meja' => 'required|string|max:100'
        ], [
            'nomor_meja.unique' => 'Nomor meja sudah digunakan'
        ]);

        $kodeMeja = 'meja-' . strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->nomor_meja)));

        $meja->update([
            'table_number' => $request->nomor_meja,
            'name' => $request->nama_meja,
            'qr_token' => $kodeMeja
        ]);

        return response()->json([
            'message' => 'Data meja berhasil diubah',
            'data' => [
                'id' => $meja->id,
                'nomor_meja' => $meja->table_number,
                'nama_meja' => $meja->name,
                'kode_meja' => $meja->qr_token,
                'aktif' => $meja->status === 'active'
            ]
        ]);
    }

    public function ubahStatusMeja(Request $request, $id)
    {
        $meja = CafeTable::find($id);

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $request->validate([
            'aktif' => 'required|boolean'
        ]);

        $meja->update([
            'status' => $request->aktif ? 'active' : 'inactive'
        ]);

        return response()->json([
            'message' => 'Status meja berhasil diubah',
            'data' => [
                'id' => $meja->id,
                'nomor_meja' => $meja->table_number,
                'nama_meja' => $meja->name,
                'kode_meja' => $meja->qr_token,
                'aktif' => $meja->status === 'active'
            ]
        ]);
    }

    public function tampilkanQrMeja($id)
    {
        $meja = CafeTable::find($id);

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $url = url('/menu/meja/' . $meja->qr_token);

        return response()->json([
            'message' => 'QR Meja berhasil di-generate',
            'data' => [
                'nomor_meja' => $meja->table_number,
                'nama_meja' => $meja->name,
                'kode_meja' => $meja->qr_token,
                'url' => $url
            ]
        ]);
    }
}
