<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssocieMouvement;
use App\Models\Associes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Associés et synthèse de leur compte courant.
 */
class AssocieController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:10',
            'sort' => 'nullable|in:nom,solde,total_credits,total_debits,part,date_adhesion,dernier_mouvement',
            'direction' => 'nullable|in:asc,desc',
        ]);

        $query = Associes::query()->withSoldes();

        if (! empty($filters['search'])) {
            $term = '%' . addcslashes($filters['search'], '%_\\') . '%';
            $query->where(function ($q) use ($term) {
                foreach (['nom', 'prenom', 'email', 'telephone', 'fonction'] as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        if (! empty($filters['devise'])) {
            $query->where('devise', $filters['devise']);
        }

        $direction = $filters['direction'] ?? 'asc';
        match ($filters['sort'] ?? 'nom') {
            'nom' => $query->orderBy('nom', $direction)->orderBy('prenom', $direction),
            'solde' => $query->orderByRaw('COALESCE(total_credits, 0) - COALESCE(total_debits, 0) ' . $direction),
            default => $query->orderBy($filters['sort'], $direction),
        };

        $associes = $query->get();

        // Totaux par devise (soldes de tous les comptes affichés)
        $totals = $associes->groupBy('devise')->map(fn ($group, $devise) => [
            'devise' => $devise,
            'total_credits' => round($group->sum('total_credits'), 2),
            'total_debits' => round($group->sum('total_debits'), 2),
            'solde' => round($group->sum('solde'), 2),
            'count' => $group->count(),
        ])->values();

        return response()->json(['data' => $associes, 'totals' => $totals]);
    }

    public function show(Associes $associe)
    {
        $associe = Associes::withSoldes()->findOrFail($associe->id);

        $parType = AssocieMouvement::where('associe_id', $associe->id)
            ->selectRaw('type, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $synthese = collect(AssocieMouvement::TYPES)->mapWithKeys(fn ($type) => [
            $type => [
                'total' => (float) ($parType[$type]->total ?? 0),
                'count' => (int) ($parType[$type]->count ?? 0),
            ],
        ]);

        return response()->json(array_merge($associe->toArray(), ['synthese' => $synthese]));
    }

    public function store(Request $request)
    {
        $associe = Associes::create($this->validated($request));

        return response()->json([
            'message' => 'Associé créé.',
            'data' => Associes::withSoldes()->find($associe->id),
        ], 201);
    }

    public function update(Request $request, Associes $associe)
    {
        $data = $this->validated($request, $associe);

        if (isset($data['devise']) && $data['devise'] !== $associe->devise && $associe->mouvements()->exists()) {
            return response()->json([
                'message' => 'Impossible de changer la devise : le compte a déjà des mouvements.',
                'errors' => ['devise' => ['Impossible de changer la devise : le compte a déjà des mouvements.']],
            ], 422);
        }

        $associe->update($data);

        return response()->json([
            'message' => 'Associé mis à jour.',
            'data' => Associes::withSoldes()->find($associe->id),
        ]);
    }

    public function destroy(Associes $associe)
    {
        if ($associe->mouvements()->exists()) {
            return response()->json([
                'message' => "Impossible de supprimer cet associé : son compte contient des mouvements. Supprimez d'abord les mouvements.",
            ], 422);
        }

        $associe->delete();

        return response()->json(['message' => 'Associé supprimé.']);
    }

    protected function validated(Request $request, ?Associes $associe = null): array
    {
        $required = $associe ? 'sometimes|required' : 'required';

        return $request->validate([
            'nom' => "$required|string|max:255",
            'prenom' => "$required|string|max:255",
            'email' => ['nullable', 'email', 'max:255', Rule::unique('associes', 'email')->ignore($associe?->id)],
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
            'fonction' => 'nullable|string|max:255',
            'part' => 'nullable|numeric|min:0|max:100',
            'devise' => 'sometimes|required|string|in:XOF,EUR,USD',
            'date_adhesion' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ], [], [
            'part' => 'part du capital',
            'date_adhesion' => "date d'adhésion",
            'adresse' => 'adresse',
            'telephone' => 'téléphone',
            'fonction' => 'fonction',
            'prenom' => 'prénom',
            'notes' => 'notes',
        ]);
    }
}
