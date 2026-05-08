<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formations\StoreFormationRequest;
use App\Http\Requests\Formations\UpdateFormationRequest;
use App\Models\Formations;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Formation",
 *     type="object",
 *     title="Formation",
 *     required={"titre", "prix"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="titre", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="objectif", type="string"),
 *     @OA\Property(property="programme", type="string"),
 *     @OA\Property(property="duree", type="integer"),
 *     @OA\Property(property="niveau", type="string"),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="prix", type="number", format="float"),
 *     @OA\Property(property="langue", type="string"),
 *     @OA\Property(property="formateur_id", type="integer"),
 *     @OA\Property(property="status", type="string"),
 * )
 */
class FormationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/formations",
     *     summary="Liste des formations",
     *     tags={"Formations"},
     *     @OA\Response(response=200, description="Liste récupérée")
     * )
     */
    public function index()
    {
        return response()->json(
            Formations::with('formateurs', 'sessions')->latest()->get(),
            200
        );
    }

    /**
     * @OA\Post(
     *     path="/api/formations",
     *     summary="Créer une formation",
     *     tags={"Formations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Formation")
     *     ),
     *     @OA\Response(response=201, description="Formation créée")
     * )
     */
    public function store(StoreFormationRequest $request)
    {
        $formation = Formations::create($request->validated());

        return response()->json([
            'message' => 'Formation créée avec succès.',
            'data' => $formation
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/formations/{id}",
     *     summary="Afficher une formation",
     *     tags={"Formations"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Données récupérées")
     * )
     */
    public function show(Formations $formation)
    {
        return response()->json(
            $formation->load('formateur', 'sessions'),
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/formations/{id}",
     *     summary="Modifier une formation",
     *     tags={"Formations"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Formation")
     *     ),
     *     @OA\Response(response=200, description="Formation mise à jour")
     * )
     */
    public function update(UpdateFormationRequest $request, Formation $formation)
    {
        $formation->update($request->validated());

        return response()->json([
            'message' => 'Formation mise à jour.',
            'data' => $formation
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/formations/{id}",
     *     summary="Supprimer une formation",
     *     tags={"Formations"},
     *     @OA\Parameter(name="id", in="path", required=true),
 *     @OA\Response(response=204, description="Formation supprimée")
     * )
     */
    public function destroy(Formations $formation)
    {
        $formation->delete();

        return response()->json(['message' => 'Formation supprimée.'], 204);
    }
}
