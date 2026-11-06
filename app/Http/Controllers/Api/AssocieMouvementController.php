<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssocieMouvement;
use App\Models\Associes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Mouvements d'un compte courant d'associé (relevé avec solde cumulé).
 */
class AssocieMouvementController extends Controller
{
    protected const EXPORT_LIMIT = 10000;

    public function index(Request $request, Associes $associe)
    {
        $filters = $this->validateFilters($request);
        $query = $this->filteredQuery($associe, $filters);

        $totals = (clone $query)
            ->reorder()
            ->setEagerLoads([])
            ->select('type')
            ->selectRaw('SUM(montant) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->type => ['total' => (float) $row->total, 'count' => (int) $row->count]]);

        $page = $query->paginate($filters['per_page'] ?? 25)->withQueryString();

        return response()->json(array_merge($page->toArray(), ['totals' => $totals]));
    }

    public function export(Request $request, Associes $associe)
    {
        $filters = $this->validateFilters($request);
        $query = $this->filteredQuery($associe, $filters);

        $count = (clone $query)->reorder()->count();
        if ($count > self::EXPORT_LIMIT) {
            return response()->json([
                'message' => "Trop de lignes à exporter ($count). Affinez les filtres (max " . self::EXPORT_LIMIT . ').',
            ], 422);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show(AssocieMouvement $mouvement)
    {
        return response()->json($mouvement->load(['preuves', 'creator:id,username'])->loadCount('preuves'));
    }

    public function store(Request $request, Associes $associe)
    {
        $mouvement = $associe->mouvements()->create([
            ...$this->validated($request),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Mouvement enregistré.',
            'data' => $mouvement->loadCount('preuves'),
        ], 201);
    }

    public function update(Request $request, AssocieMouvement $mouvement)
    {
        $mouvement->update($this->validated($request, true));

        return response()->json([
            'message' => 'Mouvement mis à jour.',
            'data' => $mouvement->loadCount('preuves'),
        ]);
    }

    public function destroy(AssocieMouvement $mouvement)
    {
        $mouvement->delete();

        return response()->json(['message' => 'Mouvement supprimé.']);
    }

    protected function validated(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return $request->validate([
            'type' => [$required, Rule::in(AssocieMouvement::TYPES)],
            'montant' => "$required|numeric|gt:0|max:999999999999",
            'date_mouvement' => "$required|date",
            'mode_paiement' => 'nullable|string|max:50',
            'reference_paiement' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
        ], [
            'type.in' => 'Le type doit être : apport, intérêts, remboursement ou retrait.',
        ], [
            'type' => 'type de mouvement',
            'date_mouvement' => 'date du mouvement',
        ]);
    }

    protected function validateFilters(Request $request): array
    {
        return $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::in(AssocieMouvement::TYPES)],
            'sens' => 'nullable|in:credit,debit',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'has_preuve' => 'nullable|boolean',
            'sort' => 'nullable|in:date,montant,type,created_at',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
    }

    /**
     * Le solde cumulé (solde_apres) est calculé sur TOUT le compte, dans l'ordre
     * chronologique, avant application des filtres : il reste juste quel que soit le filtre.
     */
    protected function filteredQuery(Associes $associe, array $filters): Builder
    {
        $signed = str_replace(['type', 'montant'], ['m2.type', 'm2.montant'], AssocieMouvement::signedAmountSql());

        $query = AssocieMouvement::query()
            ->select('associe_mouvements.*')
            ->selectRaw("(
                SELECT SUM($signed) FROM associe_mouvements AS m2
                WHERE m2.associe_id = associe_mouvements.associe_id
                  AND (m2.date_mouvement < associe_mouvements.date_mouvement
                       OR (m2.date_mouvement = associe_mouvements.date_mouvement AND m2.id <= associe_mouvements.id))
            ) AS solde_apres")
            ->where('associe_mouvements.associe_id', $associe->id)
            ->withCount('preuves');

        if (! empty($filters['search'])) {
            $term = '%' . addcslashes($filters['search'], '%_\\') . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('reference_paiement', 'like', $term)
                    ->orWhere('mode_paiement', 'like', $term)
                    ->orWhere('type', 'like', $term)
                    ->orWhere('montant', 'like', $term);
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['sens'])) {
            $query->whereIn('type', $filters['sens'] === 'credit' ? AssocieMouvement::CREDITS : AssocieMouvement::DEBITS);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('date_mouvement', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('date_mouvement', '<=', $filters['date_to']);
        }
        if (isset($filters['montant_min']) && $filters['montant_min'] !== '') {
            $query->where('montant', '>=', $filters['montant_min']);
        }
        if (isset($filters['montant_max']) && $filters['montant_max'] !== '') {
            $query->where('montant', '<=', $filters['montant_max']);
        }
        if (isset($filters['has_preuve']) && $filters['has_preuve'] !== '') {
            $filters['has_preuve'] ? $query->has('preuves') : $query->doesntHave('preuves');
        }

        $sortColumns = [
            'date' => 'date_mouvement',
            'montant' => 'montant',
            'type' => 'type',
            'created_at' => 'created_at',
        ];
        $direction = $filters['direction'] ?? 'desc';

        return $query
            ->orderBy($sortColumns[$filters['sort'] ?? 'date'], $direction)
            ->orderBy('id', $direction);
    }
}
