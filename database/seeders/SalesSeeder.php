<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample sales data
        $sales = [
            [
                'invoice_number' => 'INV-2026-001',
                'subtotal' => 15000.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 15000.00,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_amount' => 15000.00,
                'change_amount' => 0.00,
                'customer_name' => 'John Doe',
                'customer_phone' => '01712345678',
                'notes' => 'Sample sale',
                'customer_id' => null,
                'user_id' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'subtotal' => 8500.00,
                'tax_amount' => 0.00,
                'discount_amount' => 500.00,
                'total_amount' => 8000.00,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'paid_amount' => 8000.00,
                'change_amount' => 0.00,
                'customer_name' => 'Jane Smith',
                'customer_phone' => '01898765432',
                'notes' => 'Sample sale with discount',
                'customer_id' => null,
                'user_id' => 1,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'subtotal' => 22000.00,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 22000.00,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_amount' => 22000.00,
                'change_amount' => 0.00,
                'customer_name' => 'Mike Johnson',
                'customer_phone' => '01911223344',
                'notes' => 'Large sale',
                'customer_id' => null,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('sales')->insert($sales);
    }
}
