<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with(['kategori', 'modifierGroups']);

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama', 'like', '%' . $searchTerm . '%');
                
                // Jika pencarian mirip dengan kode barang BRG-xxx
                if (stripos($searchTerm, 'BRG-') !== false) {
                    $id = (int) str_ireplace('BRG-', '', $searchTerm);
                    $q->orWhere('id', $id);
                }
            });
        }

        $produks = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::where('aktif', true)->get();
        $modifierGroups = \App\Models\ModifierGroup::where('aktif', true)->get();
        $satuans = \App\Models\Satuan::where('aktif', true)->orderBy('nama')->get();
        return view('admin.produk.index', compact('produks', 'kategoris', 'modifierGroups', 'satuans'));
    }

    public function create()
    {
        $kategoris = Kategori::where('aktif', true)->get();
        $modifierGroups = \App\Models\ModifierGroup::where('aktif', true)->get();
        return view('admin.produk.create', compact('kategoris', 'modifierGroups'));
    }

    public function store(StoreProdukRequest $request)
    {
        DB::transaction(function () use ($request) {
            $produk = Produk::create($request->validated());
            $produk->modifierGroups()->sync($request->modifier_groups ?? []);
        });
        
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $kategoris = Kategori::where('aktif', true)->get();
        $modifierGroups = \App\Models\ModifierGroup::where('aktif', true)
            ->orWhereHas('produks', function ($query) use ($produk) {
                $query->where('produk_id', $produk->id);
            })->get();
        return view('admin.produk.edit', compact('produk', 'kategoris', 'modifierGroups'));
    }

    public function update(UpdateProdukRequest $request, Produk $produk)
    {
        DB::transaction(function () use ($request, $produk) {
            $produk->update($request->validated());
            
            // Preserve existing inactive modifier groups to prevent silent data loss
            $inactiveGroups = $produk->modifierGroups->where('aktif', false)->pluck('id')->toArray();
            $activeGroupsFromRequest = $request->modifier_groups ?? [];
            
            $finalGroups = array_unique(array_merge($inactiveGroups, $activeGroupsFromRequest));
            
            if (app()->runningUnitTests()) {
                \Log::info('Debug Update', ['inactive' => $inactiveGroups, 'req' => $activeGroupsFromRequest, 'final' => $finalGroups, 'all' => $produk->modifierGroups->toArray()]);
            }
            
            $produk->modifierGroups()->sync($finalGroups);
        });

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->detailPesanan()->count() > 0) {
            return redirect()->route('admin.produk.index')->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan. Pertimbangkan untuk menonaktifkannya saja.');
        }
        $produk->delete();
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
