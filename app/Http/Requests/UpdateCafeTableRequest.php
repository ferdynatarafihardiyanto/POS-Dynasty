<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCafeTableRequest extends FormRequest
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
        $meja = $this->route('meja');
        $id = $meja ? $meja->id : null;
        return [
            'table_number' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive'
        ];
    }
}
