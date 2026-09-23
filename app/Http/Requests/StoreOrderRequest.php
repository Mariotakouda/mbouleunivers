<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // accessible sans authentification (client sans compte)
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],

            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^(\+228)?[0-9]{8}$/'],
            'customer_email' => ['required', 'email', 'max:150'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Le numéro de téléphone doit être un numéro togolais valide.',
            'items.required' => 'Vous devez sélectionner au moins un billet.',
            'items.*.quantity.max' => 'Vous ne pouvez pas réserver plus de 10 billets par catégorie.',
        ];
    }

    /**
     * Vérification métier supplémentaire : disponibilité réelle des billets.
     * À appeler explicitement dans le controller après validation de base,
     * car elle dépend de plusieurs lignes (items) en même temps.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $ticketType = \App\Models\TicketType::find($item['ticket_type_id'] ?? null);

                if ($ticketType && !$ticketType->isAvailable((int) $item['quantity'])) {
                    $validator->errors()->add(
                        "items.$index.quantity",
                        "Quantité indisponible pour la catégorie \"{$ticketType->name}\"."
                    );
                }
            }
        });
    }
}
