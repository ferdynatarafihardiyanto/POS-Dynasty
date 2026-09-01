<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CafeTable;

class CafeTableController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Daftar meja',
            'data' => CafeTable::all()
        ]);
    }

    public function show($qr_token)
    {
        $table = CafeTable::where('qr_token', $qr_token)->first();

        if (!$table) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        if ($table->status !== 'active') {
            return response()->json(['message' => 'Meja sedang tidak tersedia'], 422);
        }

        return response()->json([
            'message' => 'Meja berhasil ditemukan',
            'data' => [
                'id' => $table->id,
                'nomor_meja' => $table->table_number,
                'nama_meja' => $table->name
            ]
        ]);
    }
}
