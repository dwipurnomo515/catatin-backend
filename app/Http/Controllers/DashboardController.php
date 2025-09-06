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
        Log::info('Masuk ke dashboard');

        $user = Auth::user();
        Log::info('User:', ['user' => $user]);

        if (!$user) {
            Log::warning('User null');
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        Log::info('Ambil semua transaksi...');
        $transactions = Transaction::where('user_id', $user->id)->get();
        Log::info('Total transaksi: ' . $transactions->count());

        $income = $transactions->where('type', 'income')->sum('amount');
        $expense = $transactions->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        Log::info('Mulai hitung monthly summary');
        $monthly = Transaction::selectRaw("DATE_FORMAT(date, '%Y-%m') as month, type, SUM(amount) as total")
            ->where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        $summary = [];
        foreach ($monthly as $month => $data) {
            $income = $data->where('type', 'income')->sum('total');
            $expense = $data->where('type', 'expense')->sum('total');
            $summary[] = [
                'month' => $month,
                'income' => (float) $income,
                'expense' => (float) $expense
            ];
        }

        Log::info('Selesai');

        return response()->json([
            'total_income' => (float) $income,
            'total_expense' => (float) $expense,
            'balance' => (float) $balance,
            'monthly_summary' => $summary,
        ]);
    }
}
