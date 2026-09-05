<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
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
        $kategori = $this->route('kategori');
        $id = $kategori ? $kategori->id : null;
        return [
            'nama' => 'required|string|max:255|unique:kategori,nama,' . $id,
            'deskripsi' => 'nullable|string',
            'aktif' => 'nullable|boolean'
        ];
    }
}
