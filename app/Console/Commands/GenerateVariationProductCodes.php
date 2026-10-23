<?php

namespace App\Console\Commands;

use App\Concerns\GeneratesProductIdentifiers;
use App\Models\ProductVariation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GenerateVariationProductCodes extends Command
{
    use GeneratesProductIdentifiers;

    protected $signature = 'product-variations:generate-codes';
    protected $description = 'Generate unique product_codes for all product_variations that are missing them';

    public function handle(): int
    {
        if (!Schema::hasTable('product_variations') || !Schema::hasColumn('product_variations', 'product_code')) {
            $this->error('product_variations table or product_code column does not exist.');
            return Command::FAILURE;
        }

        $missing = ProductVariation::whereNull('product_code')
            ->orWhere('product_code', '')
            ->get();

        if ($missing->isEmpty()) {
            $this->info('All product_variations already have a product_code.');
            return Command::SUCCESS;
        }

        $this->info("Found {$missing->count()} variation(s) without product_code.");
        $bar = $this->output->createProgressBar($missing->count());
        $bar->start();

        $fixed = 0;
        $usedCodes = [];

        foreach ($missing as $variation) {
            $code = $this->generateNextProductCode($usedCodes);
            $variation->update(['product_code' => $code]);
            $usedCodes[] = $code;
            $fixed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! {$fixed} variation(s) updated with unique product_codes.");

        return Command::SUCCESS;
    }
}
