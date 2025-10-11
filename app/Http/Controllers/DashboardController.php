<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $transactions = Transaction::where('user_id', $user->id)->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $monthly = Transaction::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, type, SUM(amount) as total")
            ->where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $summary = [];
        foreach ($monthly as $month => $data) {
            $monthIncome = $data->where('type', 'income')->sum('total');
            $monthExpense = $data->where('type', 'expense')->sum('total');
            $summary[] = [
                'month' => $month,
                'income' => (float) $monthIncome,
                'expense' => (float) $monthExpense
            ];
        }

        // Hitung monthly growth income
        $growth = 0;
        if (count($summary) >= 2) {
            $lastMonth = end($summary);
            $prevMonth = prev($summary);
            $prevIncome = $prevMonth['income'] ?: 1;
            $growth = (($lastMonth['income'] - $prevMonth['income']) / $prevIncome) * 100;
        }

        // Breakdown kategori pengeluaran
        $categoryBreakdown = Transaction::with('category')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->category->name ?? 'Unknown';
            })
            ->map(function ($transactions, $categoryName) use ($totalExpense) {
                $amount = $transactions->sum('amount');
                return [
                    'name' => $categoryName,
                    'amount' => (float) $amount,
                    'percentage' => $totalExpense > 0 ? round(($amount / $totalExpense) * 100, 1) : 0,
                ];
            })
            ->values();

        $recentTransactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->latest('date')
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'type' => $t->type,
                    'amount' => (float) $t->amount,
                    'category' => $t->category->name ?? 'Unknown',
                    'description' => $t->description,
                    'date' => \Carbon\Carbon::parse($t->date)->toDateString(),
                ];
            });


        return response()->json([
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'balance' => (float) $balance,
            'monthly_summary' => $summary,
            'monthly_growth' => round($growth, 2),
            'category_breakdown' => $categoryBreakdown,
            'recent_transactions' => $recentTransactions,

        ]);
    }
}
