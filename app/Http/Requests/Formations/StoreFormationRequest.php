<?php

namespace App\Http\Requests\Formations;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormationRequest extends FormRequest
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
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'objectif' => 'nullable|string',
            'programme' => 'nullable|string',
            'duree' => 'nullable|integer|min:1',
            'niveau' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'prix' => 'required|numeric|min:0',
            'formateur_id' => 'nullable|exists:formateurs,id',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'nombre_places' => 'nullable|integer|min:1',
            'status' => 'nullable|string|max:50',
        ];
    }
}
