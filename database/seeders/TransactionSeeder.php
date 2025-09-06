<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            echo "No user found. Please register a user first.\n";
            return;
        }

        $categories = [
            'income' => ['Gaji', 'Bonus', 'Penjualan', 'Investasi'],
            'expense' => ['Makan', 'Transportasi', 'Tagihan', 'Hiburan', 'Belanja']
        ];

        $faker = \Faker\Factory::create();

        foreach (range(1, 100) as $i) {
            $type = $faker->randomElement(['income', 'expense']);
            $category = $faker->randomElement($categories[$type]);
            $date = Carbon::now()->subMonths(rand(0, 11))->startOfMonth()->addDays(rand(0, 27));

            Transaction::create([
                'user_id'    => $user->id,
                'type'       => $type,
                'amount'     => $type === 'income'
                    ? $faker->numberBetween(500000, 5000000)
                    : $faker->numberBetween(20000, 2000000),
                'description' => $faker->sentence(3),
                'category'   => $category,
                'date'       => $date,
            ]);
        }

        echo "✅ Dummy transactions seeded for user {$user->email}\n";
    }
}
