<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $stock = $this->route('stock');
            $type = $this->input('type');
            $quantity = (int) $this->input('quantity');

            if (in_array($type, ['in', 'out'], true) && $quantity < 1) {
                $validator->errors()->add('quantity', 'Jumlah harus minimal 1.');
            }

            if ($type === 'out' && $stock && $quantity > $stock->quantity) {
                $validator->errors()->add('quantity', 'Stok keluar tidak boleh melebihi stok saat ini.');
            }
        }];
    }
}
