<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main Categories (Admission Types)
        $mainCategories = [
            [
                'code' => 'LAB',
                'name' => 'Laboratory Tests',
            ],
            [
                'code' => 'IMG',
                'name' => 'Imaging Services',
            ],
            [
                'code' => 'CONS',
                'name' => 'Consultation',
            ],
            [
                'code' => 'PROC',
                'name' => 'Procedures',
            ],
        ];

        foreach ($mainCategories as $category) {
            MainCategory::create($category);
        }

        // Sub Categories (Investigations)
        $subCategories = [
            // Laboratory Tests
            [
                'main_code' => 'LAB',
                'code' => 'CB',
                'name' => 'Complete Blood Count (CBC)',
                'amount' => 25.00,
            ],
            [
                'main_code' => 'LAB',
                'code' => 'LFT',
                'name' => 'Liver Function Test',
                'amount' => 35.00,
            ],
            [
                'main_code' => 'LAB',
                'code' => 'RFT',
                'name' => 'Renal Function Test',
                'amount' => 30.00,
            ],
            [
                'main_code' => 'LAB',
                'code' => 'TP',
                'name' => 'Thyroid Profile',
                'amount' => 40.00,
            ],
            [
                'main_code' => 'LAB',
                'code' => 'BS',
                'name' => 'Blood Sugar',
                'amount' => 15.00,
            ],

            // Imaging Services
            [
                'main_code' => 'IMG',
                'code' => 'XR',
                'name' => 'X-Ray',
                'amount' => 50.00,
            ],
            [
                'main_code' => 'IMG',
                'code' => 'USG',
                'name' => 'Ultrasound Sonography',
                'amount' => 75.00,
            ],
            [
                'main_code' => 'IMG',
                'code' => 'CT',
                'name' => 'CT Scan',
                'amount' => 200.00,
            ],
            [
                'main_code' => 'IMG',
                'code' => 'MRI',
                'name' => 'MRI Scan',
                'amount' => 300.00,
            ],
            [
                'main_code' => 'IMG',
                'code' => 'ECG',
                'name' => 'Electrocardiogram',
                'amount' => 35.00,
            ],

            // Consultation
            [
                'main_code' => 'CONS',
                'code' => 'GEN',
                'name' => 'General Consultation',
                'amount' => 40.00,
            ],
            [
                'main_code' => 'CONS',
                'code' => 'SPE',
                'name' => 'Specialist Consultation',
                'amount' => 75.00,
            ],
            [
                'main_code' => 'CONS',
                'code' => 'FOL',
                'name' => 'Follow-up Consultation',
                'amount' => 25.00,
            ],

            // Procedures
            [
                'main_code' => 'PROC',
                'code' => 'INJ',
                'name' => 'Injection',
                'amount' => 20.00,
            ],
            [
                'main_code' => 'PROC',
                'code' => 'DRS',
                'name' => 'Dressing',
                'amount' => 15.00,
            ],
            [
                'main_code' => 'PROC',
                'code' => 'SUTR',
                'name' => 'Suture Removal',
                'amount' => 10.00,
            ],
            [
                'main_code' => 'PROC',
                'code' => 'ECC',
                'name' => 'Ear Cleaning',
                'amount' => 25.00,
            ],
        ];

        foreach ($subCategories as $subCategory) {
            SubCategory::create($subCategory);
        }

        $this->command->info('Billing categories seeded successfully!');
    }
}
