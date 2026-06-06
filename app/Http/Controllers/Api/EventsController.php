<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Events;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     required={"title", "start_date"},
 *
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="formation_id", type="integer", nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="start_date", type="string", format="date-time"),
 *     @OA\Property(property="end_date", type="string", format="date-time"),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="is_online", type="boolean"),
 *     @OA\Property(property="online_link", type="string"),
 *     @OA\Property(property="capacity", type="integer"),
 *     @OA\Property(property="image", type="string")
 * )
 */
class EventsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Liste paginée des événements",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des événements récupérée"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $events = Events::with('formation')
            ->latest()
            ->paginate($perPage);

        return response()->json($events, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Créer un événement",
     *     tags={"Events"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Événement créé"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formation_id' => 'nullable|exists:formations,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published,cancelled,finished',
            'is_online' => 'nullable|boolean',
            'online_link' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
        ]);

        $event = Events::create($validated);

        return response()->json([
            'message' => 'Événement créé avec succès.',
            'data' => $event->load('formation')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Afficher un événement",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Événement récupéré"
     *     )
     * )
     */
    public function show(Events $event)
    {
        return response()->json(
            $event->load('formation'),
            200
        );
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Modifier un événement",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Événement mis à jour"
     *     )
     * )
     */
    public function update(Request $request, Events $event)
    {
        $validated = $request->validate([
            'formation_id' => 'nullable|exists:formations,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:draft,published,cancelled,finished',
            'is_online' => 'nullable|boolean',
            'online_link' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:255',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Événement mis à jour.',
            'data' => $event->load('formation')
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Supprimer un événement",
     *     tags={"Events"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Événement supprimé"
     *     )
     * )
     */
    public function destroy(Events $event)
    {
        $event->delete();

        return response()->json(null, 204);
    }
}