<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreModifierOptionRequest extends FormRequest
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
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'nama' => 'required|string|max:255',
            'harga_tambahan' => 'required|integer|min:0',
            'aktif' => 'boolean'
        ];
    }
}
