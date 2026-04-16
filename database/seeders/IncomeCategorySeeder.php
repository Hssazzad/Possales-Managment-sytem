<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IncomeCategory;

class IncomeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sales',
                'description' => 'Product sales revenue',
                'color' => '#28a745',
                'is_active' => true,
            ],
            [
                'name' => 'Services',
                'description' => 'Service fees and charges',
                'color' => '#007bff',
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous income sources',
                'color' => '#6c757d',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            IncomeCategory::create($category);
        }
    }
}
