<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionsExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function __construct(private $userId, private $month) {}

    public function collection()
    {
        [$year, $monthNum] = explode('-', $this->month);

        return Transaction::where('user_id', $this->userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->get();
    }
}
