<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Auth is handled by route middleware (role: admin, kasir)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'meja_id' => 'required|integer|exists:cafe_tables,id',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|integer|exists:produk,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
            'items.*.modifiers' => 'nullable|array',
            'items.*.modifiers.*' => 'integer|exists:modifier_options,id',
            'payment_method' => 'required|in:tunai,qris,debit,cash,transfer',
            'cash_received' => 'required|numeric|min:0',
        ];
    }
}
