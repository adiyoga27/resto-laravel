<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Admin Resto',
            'email' => 'admin@resto.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@resto.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Kasir,
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dapur 1',
            'email' => 'dapur@resto.test',
            'password' => bcrypt('password'),
            'role' => UserRole::Dapur,
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $categories = [
            ['name' => 'Makanan', 'description' => 'Aneka makanan berat', 'sort_order' => 1],
            ['name' => 'Minuman', 'description' => 'Aneka minuman segar', 'sort_order' => 2],
            ['name' => 'Snack', 'description' => 'Camilan ringan', 'sort_order' => 3],
            ['name' => 'Dessert', 'description' => 'Makanan penutup', 'sort_order' => 4],
        ];

        foreach ($categories as $cat) {
            MenuCategory::create(array_merge($cat, ['is_active' => true]));
        }

        $items = [
            ['name' => 'Nasi Goreng', 'category' => 1, 'price' => 25000, 'stock' => 50],
            ['name' => 'Mie Goreng', 'category' => 1, 'price' => 22000, 'stock' => 50],
            ['name' => 'Ayam Bakar', 'category' => 1, 'price' => 30000, 'stock' => 30],
            ['name' => 'Sate Ayam', 'category' => 1, 'price' => 28000, 'stock' => 40],
            ['name' => 'Es Teh', 'category' => 2, 'price' => 5000, 'stock' => 100],
            ['name' => 'Es Jeruk', 'category' => 2, 'price' => 8000, 'stock' => 80],
            ['name' => 'Kopi', 'category' => 2, 'price' => 12000, 'stock' => 60],
            ['name' => 'Kentang Goreng', 'category' => 3, 'price' => 15000, 'stock' => 40],
            ['name' => 'Pisang Goreng', 'category' => 3, 'price' => 12000, 'stock' => 40],
            ['name' => 'Es Krim', 'category' => 4, 'price' => 10000, 'stock' => 50],
        ];

        foreach ($items as $item) {
            MenuItem::create([
                'menu_category_id' => $item['category'],
                'name' => $item['name'],
                'price' => $item['price'],
                'stock' => $item['stock'],
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        for ($i = 1; $i <= 12; $i++) {
            RestaurantTable::create([
                'table_number' => 'T'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'capacity' => $i <= 4 ? 2 : ($i <= 8 ? 4 : 6),
                'status' => TableStatus::Kosong,
            ]);
        }
    }
}
