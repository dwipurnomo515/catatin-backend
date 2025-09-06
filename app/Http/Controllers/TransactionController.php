<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('start')) {
            $query->whereDate('date', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('date', '<=', $request->end);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'category' => 'nullable|string'
        ]);

        $data['user_id'] = Auth::id();

        $transaction = Transaction::create($data);

        return response()->json($transaction, 201);
    }

    public function show(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        return response()->json($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'category' => 'nullable|string'
        ]);

        $transaction->update($data);

        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorizeTransaction($transaction);

        $transaction->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function authorizeTransaction(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }
}
