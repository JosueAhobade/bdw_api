<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recettes\StoreRecetteRequest;
use App\Http\Requests\Recettes\UpdateRecetteRequest;
use App\Models\Recettes;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Recette",
 *     type="object",
 *     required={"montant", "type_recette"},
 *
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="montant", type="number", format="float"),
 *     @OA\Property(property="type_recette", type="string"),
 *     @OA\Property(property="mode_paiement", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="date_recette", type="string", format="date"),
 *     @OA\Property(property="status", type="string")
 * )
 */
class RecetteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/recettes",
     *     summary="Liste paginée des recettes",
     *     tags={"Recettes"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Numéro de page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Nombre d'éléments par page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste paginée récupérée"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $recettes = Recettes::with('employe', 'inscription')
            ->latest()
            ->paginate($perPage);

        return response()->json($recettes, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/recettes",
     *     summary="Créer une recette",
     *     tags={"Recettes"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Recette")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Recette créée"
     *     )
     * )
     */
    public function store(StoreRecetteRequest $request)
    {
        $recette = Recettes::create($request->validated());

        return response()->json([
            'message' => 'Recette créée avec succès.',
            'data' => $recette
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/recettes/{id}",
     *     summary="Afficher une recette",
     *     tags={"Recettes"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la recette",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Données récupérées"
     *     )
     * )
     */
    public function show(Recettes $recette)
    {
        return response()->json(
            $recette->load('employe', 'inscription'),
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/recettes/{id}",
     *     summary="Modifier une recette",
     *     tags={"Recettes"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la recette",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Recette")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Recette mise à jour"
     *     )
     * )
     */
    public function update(UpdateRecetteRequest $request, Recettes $recette)
    {
        $recette->update($request->validated());

        return response()->json([
            'message' => 'Recette mise à jour.',
            'data' => $recette
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/recettes/{id}",
     *     summary="Supprimer une recette",
     *     tags={"Recettes"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la recette",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Recette supprimée"
     *     )
     * )
     */
    public function destroy(Recettes $recette)
    {
        $recette->delete();

        return response()->json([
            'message' => 'Recette supprimée.'
        ], 204);
    }
}