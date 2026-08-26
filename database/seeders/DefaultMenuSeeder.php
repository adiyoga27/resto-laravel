<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            'MINUMAN' => [
                ['Americano Hot', 20000],
                ['Americano Ice', 20000],
                ['Cappucino', 20000],
                ['Latte Hot', 20000],
                ['Latte Ice', 20000],
                ['Café Mocha', 21000],
                ['Vanilla Latte Hot', 21000],
                ['Vanilla Latte Ice', 21000],
                ['Aren Latte', 21000],
                ['Shadja Butterscoth', 25000],
                ['Shadja Dolce', 25000],
                ['Shadja Matcha Hot', 25000],
                ['Shadja Matcha Ice', 25000],
                ['Chocolate Latte Hot', 20000],
                ['Chocolate Latte Ice', 20000],
                ['Ube Latte Hot', 20000],
                ['Ube Latte Ice', 20000],
                ['Milky Berry Ice', 20000],
                ['Shadja Booster Ice', 25000],
                ['Karangasem Fizz Ice', 18000],
                ['Berry Yakult Ice', 18000],
                ['Orange Squash Ice', 18000],
                ['Tea Hot', 15000],
                ['Ice Tea', 15000],
                ['Rosella Hot', 15000],
                ['Rosella Ice', 15000],
                ['Aqua', 10000],
                ['Aqua Ice', 10000],
            ],
            'MAKANAN' => [
                ['Nasi Goreng', 22000],
                ['Mie Goreng', 22000],
            ],
            'SNACK' => [
                ['Lumpia Sayur', 11000],
                ['Lumpia Ayam', 15000],
                ['Singkong Goreng', 15000],
                ['French Fries', 15000],
                ['Mix Plater', 22000],
                ['Rissoles', 25000],
                ['Chicken Wings', 30000],
            ],
            'BAKERY' => [
                ['Croffle', 13000],
                ['Brownies', 22000],
                ['Cheese Croissant', 27000],
                ['Danish Chocolate', 27000],
                ['Almond Croissant', 30000],
                ['Cheese Cake', 30000],
            ],
        ];

        $categoryOrder = 1;

        foreach ($categories as $categoryName => $items) {
            $category = MenuCategory::create([
                'name' => $categoryName,
                'description' => null,
                'is_active' => true,
                'sort_order' => $categoryOrder,
            ]);

            foreach ($items as $sortOrder => [$name, $price]) {
                MenuItem::create([
                    'menu_category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'stock' => 0,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]);
            }

            $categoryOrder++;
        }
    }
}