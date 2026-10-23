<?php

namespace Database\Seeders;

use App\Models\Vat;
use Illuminate\Database\Seeder;

class VatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vatRates = [
            [
                'name' => 'Standard VAT',
                'rate' => 15.00,
                'description' => 'Standard Value Added Tax (VAT) for most goods and services',
                'is_active' => true,
            ],
            [
                'name' => 'Reduced VAT',
                'rate' => 5.00,
                'description' => 'Reduced VAT rate for essential goods and services',
                'is_active' => true,
            ],
            [
                'name' => 'Zero VAT',
                'rate' => 0.00,
                'description' => 'Zero-rated VAT for exempt goods and services',
                'is_active' => true,
            ],
            [
                'name' => 'Special VAT',
                'rate' => 25.00,
                'description' => 'Special VAT rate for specific goods and services',
                'is_active' => false,
            ],
        ];

        foreach ($vatRates as $vatRate) {
            Vat::create($vatRate);
        }
    }
}
