<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filtres, tri et recherche communs aux recettes et aux dépenses.
 *
 * Le contrôleur qui l'utilise doit définir :
 *  - modelClass()  : classe Eloquent
 *  - dateColumn()  : date_depense / date_recette
 *  - typeColumn()  : type_depense / type_recette
 *  - relations()   : relations à charger
 *
 * Paramètres de requête acceptés :
 *  search, status, devise, mode_paiement, type, employe_id,
 *  date_from, date_to, montant_min, montant_max, has_preuve (0|1),
 *  sort (date|montant|type|status|created_at|id), direction (asc|desc), per_page.
 */
trait FiltersOperations
{
    abstract protected function modelClass(): string;

    abstract protected function dateColumn(): string;

    abstract protected function typeColumn(): string;

    abstract protected function relations(): array;

    protected const EXPORT_LIMIT = 10000;

    protected function validateFilters(Request $request): array
    {
        return $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'devise' => 'nullable|string|max:10',
            'mode_paiement' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:255',
            'employe_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'montant_min' => 'nullable|numeric|min:0',
            'montant_max' => 'nullable|numeric|min:0',
            'has_preuve' => 'nullable|boolean',
            'sort' => 'nullable|in:date,montant,type,status,mode_paiement,created_at,id',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
    }

    protected function filteredQuery(array $filters): Builder
    {
        $model = $this->modelClass();
        $dateColumn = $this->dateColumn();
        $typeColumn = $this->typeColumn();

        $query = $model::query()
            ->with($this->relations())
            ->withCount('preuves');

        if (! empty($filters['search'])) {
            $term = '%' . addcslashes($filters['search'], '%_\\') . '%';
            $query->where(function (Builder $q) use ($term, $typeColumn) {
                $q->where($typeColumn, 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('reference_paiement', 'like', $term)
                    ->orWhere('mode_paiement', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('montant', 'like', $term)
                    ->orWhereHas('employe', function (Builder $e) use ($term) {
                        $e->where('nom', 'like', $term)->orWhere('prenom', 'like', $term);
                    });
            });
        }

        foreach (['status', 'devise', 'mode_paiement', 'employe_id'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        if (! empty($filters['type'])) {
            $query->where($typeColumn, $filters['type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate($dateColumn, '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate($dateColumn, '<=', $filters['date_to']);
        }

        if (isset($filters['montant_min']) && $filters['montant_min'] !== '') {
            $query->where('montant', '>=', $filters['montant_min']);
        }
        if (isset($filters['montant_max']) && $filters['montant_max'] !== '') {
            $query->where('montant', '<=', $filters['montant_max']);
        }

        if (isset($filters['has_preuve']) && $filters['has_preuve'] !== '') {
            $filters['has_preuve']
                ? $query->has('preuves')
                : $query->doesntHave('preuves');
        }

        $sortColumns = [
            'date' => $dateColumn,
            'montant' => 'montant',
            'type' => $typeColumn,
            'status' => 'status',
            'mode_paiement' => 'mode_paiement',
            'created_at' => 'created_at',
            'id' => 'id',
        ];
        $sort = $sortColumns[$filters['sort'] ?? 'date'];
        $direction = $filters['direction'] ?? 'desc';

        return $query->orderBy($sort, $direction)->orderBy('id', $direction);
    }

    /**
     * Liste paginée + totaux sur l'ensemble filtré.
     */
    protected function paginatedResponse(Request $request)
    {
        $filters = $this->validateFilters($request);
        $query = $this->filteredQuery($filters);

        $totals = (clone $query)
            ->reorder()
            ->setEagerLoads([])
            ->select('devise')
            ->selectRaw('SUM(montant) as total, COUNT(*) as count')
            ->groupBy('devise')
            ->get()
            ->map(fn ($row) => [
                'devise' => $row->devise,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])
            ->values();

        $page = $query->paginate($filters['per_page'] ?? 15)->withQueryString();

        return response()->json(array_merge($page->toArray(), ['totals' => $totals]));
    }

    /**
     * Toutes les lignes filtrées (sans pagination) pour l'export Excel / CSV.
     */
    protected function exportResponse(Request $request)
    {
        $filters = $this->validateFilters($request);
        $query = $this->filteredQuery($filters);

        $count = (clone $query)->reorder()->count();
        if ($count > self::EXPORT_LIMIT) {
            return response()->json([
                'message' => "Trop de lignes à exporter ($count). Affinez les filtres (max " . self::EXPORT_LIMIT . ').',
            ], 422);
        }

        return response()->json(['data' => $query->get()]);
    }

    /**
     * Valeurs distinctes pour alimenter les listes de filtres.
     */
    protected function optionsResponse()
    {
        $model = $this->modelClass();
        $distinct = fn (string $column) => $model::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values();

        return response()->json([
            'types' => $distinct($this->typeColumn()),
            'status' => $distinct('status'),
            'devises' => $distinct('devise'),
            'modes_paiement' => $distinct('mode_paiement'),
        ]);
    }
}
