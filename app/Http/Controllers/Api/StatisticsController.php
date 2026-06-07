<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recettes;
use App\Models\Depenses;
use App\Models\Events;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="MonthlyStatistic",
 *     type="object",
 *     @OA\Property(property="month", type="integer", example=1),
 *     @OA\Property(property="recettes", type="number", format="float", example=150000),
 *     @OA\Property(property="depenses", type="number", format="float", example=75000)
 * )
 *
 * @OA\Schema(
 *     schema="StatisticsResponse",
 *     type="object",
 *     @OA\Property(property="total_recettes", type="number", format="float", example=1250000),
 *     @OA\Property(property="total_depenses", type="number", format="float", example=450000),
 *     @OA\Property(property="solde", type="number", format="float", example=800000),
 *     @OA\Property(property="events_count", type="integer", example=12),
 *
 *     @OA\Property(
 *         property="monthly",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/MonthlyStatistic")
 *     ),
 *
 *     @OA\Property(
 *         property="upcoming_events",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Event")
 *     ),
 *
 *     @OA\Property(
 *         property="recent_recettes",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Recette")
 *     ),
 *
 *     @OA\Property(
 *         property="recent_depenses",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Depense")
 *     )
 * )
 */
class StatisticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/statistics",
     *     summary="Récupérer les statistiques globales",
     *     description="Retourne les statistiques générales de l'application : total des recettes, total des dépenses, solde, nombre d'événements, évolution mensuelle, derniers mouvements et événements à venir.",
     *     tags={"Statistics"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/StatisticsResponse")
     *     )
     * )
     */
    public function index()
    {
        $totalRecettes = Recettes::sum('montant');
        $totalDepenses = Depenses::sum('montant');

        $monthlyRecettes = Recettes::select(
                DB::raw('MONTH(date_recette) as month'),
                DB::raw('SUM(montant) as total')
            )
            ->groupBy(DB::raw('MONTH(date_recette)'))
            ->pluck('total', 'month');

        $monthlyDepenses = Depenses::select(
                DB::raw('MONTH(date_depense) as month'),
                DB::raw('SUM(montant) as total')
            )
            ->groupBy(DB::raw('MONTH(date_depense)'))
            ->pluck('total', 'month');

        $monthly = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthly[] = [
                'month' => $i,
                'recettes' => (float) ($monthlyRecettes[$i] ?? 0),
                'depenses' => (float) ($monthlyDepenses[$i] ?? 0),
            ];
        }

        return response()->json([
            'total_recettes' => (float) $totalRecettes,
            'total_depenses' => (float) $totalDepenses,
            'solde' => (float) ($totalRecettes - $totalDepenses),
            'events_count' => Events::count(),

            'monthly' => $monthly,

            'upcoming_events' => Events::whereDate('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(5)
                ->get(),

            'recent_recettes' => Recettes::latest()
                ->limit(5)
                ->get(),

            'recent_depenses' => Depenses::latest()
                ->limit(5)
                ->get(),
        ]);
    }
}