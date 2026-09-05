<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModifierGroupRequest extends FormRequest
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
        $group = $this->route('modifier_group');
        $id = $group ? $group->id : null;
        return [
            'nama' => 'required|string|max:255|unique:modifier_groups,nama,' . $id,
            'tipe' => 'required|in:single,multiple',
            'wajib_diisi' => 'boolean',
            'min_pilihan' => 'integer|min:0',
            'max_pilihan' => 'integer|min:1',
            'aktif' => 'boolean'
        ];
    }
}
