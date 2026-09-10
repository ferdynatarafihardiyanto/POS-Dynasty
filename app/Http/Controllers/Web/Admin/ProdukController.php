<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Schema;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            $hasTipeProduk = Schema::hasColumn('produk', 'tipe_produk');
            $hasBundleTable = Schema::hasTable('produk_bundle_items');
        } catch (\Throwable $e) {
            $hasTipeProduk = false;
            $hasBundleTable = false;
        }

        $relations = ['kategori', 'modifierGroups'];
        if ($hasBundleTable) {
            $relations[] = 'bundleItems';
        }

        $query = Produk::with($relations);

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
        
        $allProduksQuery = Produk::where('aktif', true);
        if ($hasTipeProduk) {
            $allProduksQuery->where('tipe_produk', 'standar');
        }
        $allProduks = $allProduksQuery->get();

        return view('admin.produk.index', compact('produks', 'kategoris', 'modifierGroups', 'satuans', 'allProduks'));
    }

    public function exportPdf(Request $request)
    {
        $query = Produk::with(['kategori', 'modifierGroups']);

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
                if (stripos($search, 'BRG-') !== false) {
                    $id = (int) str_ireplace('BRG-', '', $search);
                    $q->orWhere('id', $id);
                }
            });
        }

        $produks   = $query->latest()->get();
        $kategoris = Kategori::where('aktif', true)->get();
        $filters   = [
            'kategori' => $request->kategori ? $kategoris->find($request->kategori)?->nama : null,
            'search'   => $request->search,
        ];

        $html = view('admin.produk.pdf', compact('produks', 'filters'))->render();

        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            return redirect()->back()->with('error', 'Paket DomPDF belum terpasang di server. Silakan jalankan composer install di server.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'sans-serif');

        $filename = 'daftar-barang-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }

    public function create()
    {
        $kategoris = Kategori::where('aktif', true)->get();
        $modifierGroups = \App\Models\ModifierGroup::where('aktif', true)->get();
        $allProduks = Produk::where('tipe_produk', 'standar')->where('aktif', true)->get();
        return view('admin.produk.create', compact('kategoris', 'modifierGroups', 'allProduks'));
    }

    public function store(StoreProdukRequest $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            if ($request->hasFile('gambar')) {
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }
            $data['tipe_produk'] = $request->tipe_produk ?? 'standar';
            $produk = Produk::create($data);
            $produk->modifierGroups()->sync($request->modifier_groups ?? []);

            if ($data['tipe_produk'] === 'bundling' && $request->has('bundle_items')) {
                foreach ($request->bundle_items as $itemId => $jumlah) {
                    if ($jumlah > 0) {
                        \App\Models\ProdukBundleItem::create([
                            'bundle_id' => $produk->id,
                            'item_id' => $itemId,
                            'jumlah' => $jumlah
                        ]);
                    }
                }
            }
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
        $allProduks = Produk::where('tipe_produk', 'standar')->where('id', '!=', $produk->id)->get();
        return view('admin.produk.edit', compact('produk', 'kategoris', 'modifierGroups', 'allProduks'));
    }

    public function update(UpdateProdukRequest $request, Produk $produk)
    {
        DB::transaction(function () use ($request, $produk) {
            $data = $request->validated();
            if ($request->hasFile('gambar')) {
                if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                    Storage::disk('public')->delete($produk->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('produk', 'public');
            }
            $data['tipe_produk'] = $request->tipe_produk ?? 'standar';
            $produk->update($data);
            
            // Preserve existing inactive modifier groups to prevent silent data loss
            $inactiveGroups = $produk->modifierGroups->where('aktif', false)->pluck('id')->toArray();
            $activeGroupsFromRequest = $request->modifier_groups ?? [];
            
            $finalGroups = array_unique(array_merge($inactiveGroups, $activeGroupsFromRequest));
            
            if (app()->runningUnitTests()) {
                \Log::info('Debug Update', ['inactive' => $inactiveGroups, 'req' => $activeGroupsFromRequest, 'final' => $finalGroups, 'all' => $produk->modifierGroups->toArray()]);
            }
            
            $produk->modifierGroups()->sync($finalGroups);

            // Handle Bundle Items
            if ($data['tipe_produk'] === 'bundling') {
                \App\Models\ProdukBundleItem::where('bundle_id', $produk->id)->delete();
                if ($request->has('bundle_items')) {
                    foreach ($request->bundle_items as $itemId => $jumlah) {
                        if ($jumlah > 0) {
                            \App\Models\ProdukBundleItem::create([
                                'bundle_id' => $produk->id,
                                'item_id' => $itemId,
                                'jumlah' => $jumlah
                            ]);
                        }
                    }
                }
            } else {
                \App\Models\ProdukBundleItem::where('bundle_id', $produk->id)->delete();
            }
        });

        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        if ($produk->detailPesanan()->count() > 0) {
            return redirect()->route('admin.produk.index')->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat pesanan. Pertimbangkan untuk menonaktifkannya saja.');
        }
        if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
            Storage::disk('public')->delete($produk->gambar);
        }
        $produk->delete();
        return redirect()->route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
