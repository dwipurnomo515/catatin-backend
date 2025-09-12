<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            ['name' => 'Gaji', 'type' => 'income'],
            ['name' => 'Bonus', 'type' => 'income'],
            ['name' => 'Penjualan', 'type' => 'income'],
            ['name' => 'Investasi', 'type' => 'income'],
            ['name' => 'Makan', 'type' => 'expense'],
            ['name' => 'Transportasi', 'type' => 'expense'],
            ['name' => 'Tagihan', 'type' => 'expense'],
            ['name' => 'Belanja', 'type' => 'expense'],
        ];

        foreach ($defaultCategories as $item) {
            Category::firstOrCreate([
                'user_id' => null, // GLOBAL CATEGORY
                'name' => $item['name'],
                'type' => $item['type'],
            ]);
        }

        echo "✅ Global default categories inserted\n";
    }
}
