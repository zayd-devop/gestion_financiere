<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function kpis(Request $request)
    {
        $user = $request->user();

        // Calcul des totaux globaux
        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');

        // Le solde est la différence entre les revenus et les dépenses
        $currentBalance = $totalIncome - $totalExpense;

        return response()->json([
            'current_balance' => $currentBalance,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
        ]);
    }

    public function charts(Request $request)
    {
        $user = $request->user();

        // 1. Dépenses par catégories (pour un graphique en camembert par exemple)
        $expensesByCategory = $user->transactions()
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // 2. Évolution mensuelle des revenus et dépenses (pour un graphique en ligne/barres)
        // Utilisation de DATE_FORMAT (adapté pour MySQL)
        $monthlyEvolution = $user->transactions()
            ->select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                'type',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month', 'type')
            ->orderBy('month', 'asc')
            ->get();

        // Formatage de l'évolution mensuelle pour faciliter l'intégration frontend
        $formattedEvolution = $monthlyEvolution->groupBy('month')->map(function ($items, $month) {
            $income = $items->where('type', 'income')->first()->total ?? 0;
            $expense = $items->where('type', 'expense')->first()->total ?? 0;
            return [
                'month' => $month,
                'income' => (float) $income,
                'expense' => (float) $expense,
            ];
        })->values();

        return response()->json([
            'expenses_by_category' => $expensesByCategory,
            'monthly_evolution' => $formattedEvolution,
        ]);
    }
}
