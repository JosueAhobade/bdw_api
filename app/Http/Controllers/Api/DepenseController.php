<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\FiltersOperations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Depenses\StoreDepenseRequest;
use App\Http\Requests\Depenses\UpdateDepenseRequest;
use App\Models\Depenses;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    use FiltersOperations;

    protected function modelClass(): string
    {
        return Depenses::class;
    }

    protected function dateColumn(): string
    {
        return 'date_depense';
    }

    protected function typeColumn(): string
    {
        return 'type_depense';
    }

    protected function relations(): array
    {
        return ['employe:id,nom,prenom'];
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

    public function store(StoreDepenseRequest $request)
    {
        $depense = Depenses::create($request->validated());

        return response()->json([
            'message' => 'Dépense créée avec succès.',
            'data' => $depense->load($this->relations())->loadCount('preuves'),
        ], 201);
    }

    public function show(Depenses $depense)
    {
        return response()->json(
            $depense->load([...$this->relations(), 'preuves'])->loadCount('preuves'),
            200
        );
    }

    public function update(UpdateDepenseRequest $request, Depenses $depense)
    {
        $depense->update($request->validated());

        return response()->json([
            'message' => 'Dépense mise à jour.',
            'data' => $depense->load($this->relations())->loadCount('preuves'),
        ], 200);
    }

    public function destroy(Depenses $depense)
    {
        $depense->delete();

        return response()->json([
            'message' => 'Dépense supprimée.'
        ], 200);
    }
}
