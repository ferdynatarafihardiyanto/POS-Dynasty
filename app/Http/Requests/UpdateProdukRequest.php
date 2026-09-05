<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProdukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'hpp' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'aktif' => 'nullable|boolean',
            'modifier_groups' => 'nullable|array',
            'modifier_groups.*' => 'integer|distinct|exists:modifier_groups,id'
        ];
    }
}
