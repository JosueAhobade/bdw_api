<?php

namespace App\Http\Requests\Depenses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'montant' => 'sometimes|numeric|min:0',
            'devise' => 'sometimes|string|max:10',
            'mode_paiement' => 'sometimes|string|max:50',
            'reference_paiement' => 'sometimes|string|max:255',
            'type_depense' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'date_depense' => 'sometimes|date',
            'employe_id' => 'nullable|exists:employes,id',
            'status' => 'sometimes|string|max:50',
        ];
    }
}
