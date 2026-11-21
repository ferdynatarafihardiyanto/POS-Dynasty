<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProdukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $tipe = $this->tipe_produk ?? 'satuan';
        if ($tipe === 'standar') {
            $tipe = 'satuan';
        }

        $mergeData = [
            'aktif' => $this->has('aktif') ? true : false,
            'tipe_produk' => $tipe,
        ];

        // Jika produk resep, default hpp dan stok ke 0 jika belum diset
        if ($tipe === 'resep') {
            if (!$this->filled('hpp')) {
                $mergeData['hpp'] = 0;
            }
            if (!$this->filled('stok')) {
                $mergeData['stok'] = 0;
            }
        }

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'hpp' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'min_stok' => 'nullable|integer|min:0',
            'aktif' => 'nullable|boolean',
            'tipe_produk' => 'nullable|string|in:satuan,resep,standar,bundling',
            'modifier_groups' => 'nullable|array',
            'modifier_groups.*' => 'integer|distinct|exists:modifier_groups,id',
            'bundle_items' => 'nullable|array',
            'bundle_items.*' => 'integer|min:0'
        ];
    }
}
