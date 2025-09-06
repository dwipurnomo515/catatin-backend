<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

    public function monthly(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        try {
            $parsed = \Carbon\Carbon::parse($month . '-01');
            $year = $parsed->year;
            $monthNum = $parsed->month;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format bulan tidak valid. Gunakan format YYYY-MM'], 422);
        }

        $transactions = Transaction::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $byCategory = [
            'income' => [],
            'expense' => []
        ];

        foreach (['income', 'expense'] as $type) {
            $grouped = $transactions->where('type', $type)->groupBy('category');
            foreach ($grouped as $category => $items) {
                $byCategory[$type][$category ?? 'Lainnya'] = $items->sum('amount');
            }
        }

        return response()->json([
            'month' => "$year-" . str_pad($monthNum, 2, '0', STR_PAD_LEFT),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'by_category' => $byCategory,
        ]);
    }


    public function export(Request $request)
    {
        $format = $request->query('format', 'excel');
        $month = $request->query('month', now()->format('Y-m'));
        $fileName = "report-$month." . ($format === 'pdf' ? 'pdf' : 'xlsx');

        $export = new TransactionsExport(Auth::id(), $month);

        if ($format === 'pdf') {
            return Excel::download($export, $fileName, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download($export, $fileName);
    }
}
