<?php

namespace App\Concerns;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Schema;

trait GeneratesProductIdentifiers
{
    /**
     * Sanitize barcode/code by removing all non-alphanumeric characters.
     * Keeps only A-Z and 0-9.
     */
    protected function sanitizeBarcodeCode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return preg_replace('/[^A-Z0-9]/i', '', strtoupper($value));
    }

    protected string $codePrefix = 'BB';
    protected string $barcodePrefix = 'BB';
    protected int $barcodeBase = 250000000;

    /**
     * Format product code from sequence number N:
     * N = 1   => BB01
     * N = 9   => BB09
     * N = 12  => BB12
     * N = 100 => BB100
     */
    protected function formatProductCode(int $sequence): string
    {
        return $sequence < 10
            ? $this->codePrefix . '0' . $sequence
            : $this->codePrefix . $sequence;
    }

    /**
     * Format barcode from sequence number N:
     * N = 1   => BB250000001
     * N = 12  => BB250000012
     * N = 100 => BB250000100
     */
    protected function formatBarcode(int $sequence): string
    {
        $number = $this->barcodeBase + $sequence;
        return $this->barcodePrefix . $number;
    }

    /**
     * Extract sequence number N from a product code (e.g. BB01 => 1, BB12 => 12, BB012 => 12)
     */
    protected function extractSequenceFromCode(?string $code): int
    {
        $code = $this->sanitizeBarcodeCode($code);
        if (!$code) {
            return 0;
        }

        if (preg_match('/^' . preg_quote($this->codePrefix, '/') . '(\d+)$/i', $code, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Extract sequence number N from a barcode (e.g. BB250000001 => 1, BB250000012 => 12)
     */
    protected function extractSequenceFromBarcode(?string $barcode): int
    {
        $barcode = $this->sanitizeBarcodeCode($barcode);
        if (!$barcode) {
            return 0;
        }

        if (preg_match('/^' . preg_quote($this->barcodePrefix, '/') . '(\d+)$/i', $barcode, $matches)) {
            $val = (int) $matches[1];
            if ($val >= $this->barcodeBase) {
                return $val - $this->barcodeBase;
            }
            return $val;
        }

        return 0;
    }

    /**
     * Find next available sequential number N where both code and barcode are free.
     */
    protected function getNextIdentifierSequence(array $exclude = []): int
    {
        $maxSeq = 0;

        // Parse excludes
        $excludedSeqs = [];
        foreach ($exclude as $ex) {
            if (is_numeric($ex)) {
                $val = (int) $ex;
                $excludedSeqs[] = ($val >= $this->barcodeBase) ? ($val - $this->barcodeBase) : $val;
            } else {
                $cSeq = $this->extractSequenceFromCode($ex);
                $bSeq = $this->extractSequenceFromBarcode($ex);
                if ($cSeq > 0) $excludedSeqs[] = $cSeq;
                if ($bSeq > 0) $excludedSeqs[] = $bSeq;
            }
        }

        // Products table
        $productCodes = Product::pluck('code');
        foreach ($productCodes as $code) {
            $maxSeq = max($maxSeq, $this->extractSequenceFromCode($code));
        }
        $productBarcodes = Product::pluck('barcode');
        foreach ($productBarcodes as $bc) {
            $maxSeq = max($maxSeq, $this->extractSequenceFromBarcode($bc));
        }

        // Variations table
        if (Schema::hasTable('product_variations')) {
            if (Schema::hasColumn('product_variations', 'product_code')) {
                $varCodes = ProductVariation::pluck('product_code');
                foreach ($varCodes as $code) {
                    $maxSeq = max($maxSeq, $this->extractSequenceFromCode($code));
                }
            }
            if (Schema::hasColumn('product_variations', 'barcode')) {
                $varBarcodes = ProductVariation::pluck('barcode');
                foreach ($varBarcodes as $bc) {
                    $maxSeq = max($maxSeq, $this->extractSequenceFromBarcode($bc));
                }
            }
        }

        // Units table
        if (Schema::hasTable('product_units')) {
            if (Schema::hasColumn('product_units', 'product_code')) {
                $unitCodes = ProductUnit::pluck('product_code');
                foreach ($unitCodes as $code) {
                    $maxSeq = max($maxSeq, $this->extractSequenceFromCode($code));
                }
            }
            if (Schema::hasColumn('product_units', 'barcode')) {
                $unitBarcodes = ProductUnit::pluck('barcode');
                foreach ($unitBarcodes as $bc) {
                    $maxSeq = max($maxSeq, $this->extractSequenceFromBarcode($bc));
                }
            }
        }

        foreach ($excludedSeqs as $exSeq) {
            $maxSeq = max($maxSeq, $exSeq);
        }

        $next = $maxSeq + 1;

        while ($this->sequenceExists($next, $excludedSeqs)) {
            $next++;
        }

        return $next;
    }

    protected function sequenceExists(int $sequence, array $excludedSeqs = []): bool
    {
        if (in_array($sequence, $excludedSeqs, true)) {
            return true;
        }

        $code = $this->formatProductCode($sequence);
        $barcode = $this->formatBarcode($sequence);

        if ($this->productCodeExists($code) || $this->barcodeExists($barcode)) {
            return true;
        }

        return false;
    }

    /**
     * Generate the next synchronized code & barcode pair sharing the exact same sequence N.
     */
    protected function generateNextIdentifierPair(array $exclude = []): array
    {
        $seq = $this->getNextIdentifierSequence($exclude);

        return [
            'sequence'     => $seq,
            'product_code' => $this->formatProductCode($seq),
            'barcode'      => $this->formatBarcode($seq),
        ];
    }

    /**
     * Auto-generate the next barcode (synchronized with code sequence).
     */
    protected function generateNextBarcode(array $exclude = []): string
    {
        return $this->generateNextIdentifierPair($exclude)['barcode'];
    }

    /**
     * Auto-generate the next product code (synchronized with barcode sequence).
     */
    protected function generateNextProductCode(array $exclude = []): string
    {
        return $this->generateNextIdentifierPair($exclude)['product_code'];
    }

    protected function generateNextCode(array $exclude = []): string
    {
        return $this->generateNextProductCode($exclude);
    }

    protected function productCodeNumber(?string $code, string $prefix = 'BB'): int
    {
        return $this->extractSequenceFromCode($code);
    }

    protected function barcodeNumber(?string $barcode, string $prefix = 'BB'): int
    {
        return $this->extractSequenceFromBarcode($barcode);
    }

    protected function productCodeExists(string $code, ?int $ignoreVariationId = null, array $exclude = []): bool
    {
        if (in_array($code, $exclude, true)) {
            return true;
        }

        if (Product::where('code', $code)->exists()) {
            return true;
        }

        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'product_code')) {
            if (ProductVariation::where('product_code', $code)
                ->when($ignoreVariationId, function ($query) use ($ignoreVariationId) {
                    $query->where('id', '!=', $ignoreVariationId);
                })
                ->exists()) {
                return true;
            }
        }

        if (Schema::hasTable('product_units') && Schema::hasColumn('product_units', 'product_code')) {
            return ProductUnit::where('product_code', $code)->exists();
        }

        return false;
    }

    protected function barcodeExists(string $barcode, array $exclude = []): bool
    {
        if (in_array($barcode, $exclude, true)) {
            return true;
        }

        if (Product::where('barcode', $barcode)->exists()) {
            return true;
        }

        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'barcode')) {
            if (ProductVariation::where('barcode', $barcode)->exists()) {
                return true;
            }
        }

        if (Schema::hasTable('product_units') && Schema::hasColumn('product_units', 'barcode')) {
            return ProductUnit::where('barcode', $barcode)->exists();
        }

        return false;
    }
}