<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\FiltersOperations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recettes\StoreRecetteRequest;
use App\Http\Requests\Recettes\UpdateRecetteRequest;
use App\Models\Recettes;
use Illuminate\Http\Request;

class RecetteController extends Controller
{
    use FiltersOperations;

    protected function modelClass(): string
    {
        return Recettes::class;
    }

    protected function dateColumn(): string
    {
        return 'date_recette';
    }

    protected function typeColumn(): string
    {
        return 'type_recette';
    }

    protected function relations(): array
    {
        return ['employe:id,nom,prenom', 'inscription'];
    }

    public function index(Request $request)
    {
        return $this->paginatedResponse($request);
    }

    public function export(Request $request)
    {
        return $this->exportResponse($request);
    }

    public function options()
    {
        return $this->optionsResponse();
    }

    public function store(StoreRecetteRequest $request)
    {
        $recette = Recettes::create($request->validated());

        return response()->json([
            'message' => 'Recette créée avec succès.',
            'data' => $recette->load($this->relations())->loadCount('preuves'),
        ], 201);
    }

    public function show(Recettes $recette)
    {
        return response()->json(
            $recette->load([...$this->relations(), 'preuves'])->loadCount('preuves'),
            200
        );
    }

    public function update(UpdateRecetteRequest $request, Recettes $recette)
    {
        $recette->update($request->validated());

        return response()->json([
            'message' => 'Recette mise à jour.',
            'data' => $recette->load($this->relations())->loadCount('preuves'),
        ], 200);
    }

    public function destroy(Recettes $recette)
    {
        $recette->delete();

        return response()->json([
            'message' => 'Recette supprimée.'
        ], 200);
    }
}
