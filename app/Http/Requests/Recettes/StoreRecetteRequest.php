<?php

namespace App\Http\Requests\Recettes;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecetteRequest extends FormRequest
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
            'montant' => 'required|numeric|min:0',
            'devise' => 'nullable|string|max:10',
            'mode_paiement' => 'nullable|string|max:50',
            'reference_paiement' => 'nullable|string|max:255',
            'type_recette' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_recette' => 'required|date',
            'inscription_id' => 'nullable|exists:inscriptions,id',
            'employe_id' => 'nullable|exists:employes,id',
            'status' => 'nullable|string|max:50',
        ];
    }
}
