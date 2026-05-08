<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Depenses\StoreDepenseRequest;
use App\Http\Requests\Depenses\UpdateDepenseRequest;
use App\Models\Depenses;

/**
 * @OA\Schema(
 *     schema="Depense",
 *     type="object",
 *     title="Depense",
 *     required={"montant","type_depense"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="montant", type="number", format="float"),
 *     @OA\Property(property="type_depense", type="string"),
 *     @OA\Property(property="date_depense", type="string", format="date"),
 *     @OA\Property(property="status", type="string"),
 * )
 */

class DepenseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/depenses",
     *     summary="Liste des dépenses",
     *     tags={"Dépenses"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée"
     *     )
     * )
     */
    public function index()
    {
        return response()->json(
            Depenses::with('employe')->latest()->get(),
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/depenses",
     *     summary="Créer une dépense",
     *     tags={"Dépenses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Depense")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dépense créée"
     *     )
     * )
     */
    public function store(StoreDepenseRequest $request)
    {
        $depense = Depenses::create($request->validated());

        return response()->json([
            'message' => 'Dépense créée avec succès.',
            'data' => $depense
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/depenses/{id}",
     *     summary="Afficher une dépense",
     *     tags={"Dépenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la dépense"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Données récupérées"
     *     )
     * )
     */
    public function show(Depenses $depense)
    {
        return response()->json($depense->load('employe'), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/depenses/{id}",
     *     summary="Modifier une dépense",
     *     tags={"Dépenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la dépense"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Depense")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dépense mise à jour"
     *     )
     * )
     */
    public function update(UpdateDepenseRequest $request, Depenses $depense)
    {
        $depense->update($request->validated());

        return response()->json([
            'message' => 'Dépense mise à jour.',
            'data' => $depense
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/depenses/{id}",
     *     summary="Supprimer une dépense",
     *     tags={"Dépenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la dépense"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Dépense supprimée"
     *     )
     * )
     */
    public function destroy(Depenses $depense)
    {
        $depense->delete();

        return response()->json([
            'message' => 'Dépense supprimée.'
        ], 204);
    }
}
