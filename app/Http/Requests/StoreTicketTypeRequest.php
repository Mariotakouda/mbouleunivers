<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'available_quantity' => ['required', 'integer', 'min:0', 'lte:quantity'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'available_quantity.lte' => 'La quantité disponible ne peut pas dépasser la quantité totale.',
        ];
    }

    /**
     * Sur création, force available_quantity = quantity par défaut si non fourni.
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && !$this->filled('available_quantity')) {
            $this->merge(['available_quantity' => $this->input('quantity')]);
        }
    }
}
