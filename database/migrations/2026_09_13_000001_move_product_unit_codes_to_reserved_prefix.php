<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unit codes are internal per-item ledger serials, NOT business
     * identifiers. They must never share the BB{seq} namespace with products
     * (and variations), otherwise a single value can identify two different
     * products. BB12, BB13 and BB14 were doing exactly that: BB13 was a real
     * product code while also being the unit code of a different product.
     *
     * Unit codes therefore move to a reserved 'U' prefix, leaving BB{seq}
     * exclusively to products.code.
     */
    private const UNIT_PREFIX = 'U';

    private const SERIAL_LENGTH = 5;

    public function up(): void
    {
        if (!Schema::hasTable('product_units')) {
            return;
        }

        $used = [];
        foreach (['unit_code', 'product_code'] as $column) {
            if (!Schema::hasColumn('product_units', $column)) {
                continue;
            }
            DB::table('product_units')
                ->whereNotNull($column)
                ->pluck($column)
                ->each(function ($value) use (&$used): void {
                    $used[strtoupper((string) $value)] = true;
                });
        }

        $maxSerial = 0;

        DB::table('product_units')
            ->select('id', 'unit_code', 'product_code')
            ->orderBy('id')
            ->chunkById(100, function ($units) use (&$used, &$maxSerial): void {
                foreach ($units as $unit) {
                    $serial = $this->serialFrom($unit->unit_code)
                        ?: $this->serialFrom($unit->product_code)
                        ?: ($maxSerial + 1);

                    while (isset($used[$this->formatUnitCode($serial)])) {
                        $serial++;
                    }

                    $code = $this->formatUnitCode($serial);
                    $used[$code] = true;
                    $maxSerial = max($maxSerial, $serial);

                    DB::table('product_units')->where('id', $unit->id)->update([
                        'unit_code' => $code,
                        'product_code' => $code,
                        'updated_at' => now(),
                    ]);
                }
            }, 'id');

        if ($maxSerial > 0 && Schema::hasTable('unit_code_sequences')) {
            DB::table('unit_code_sequences')->updateOrInsert(
                ['sequence_key' => 'product_unit'],
                [
                    'current_value' => $maxSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_units')) {
            return;
        }

        // Best effort only: restoring the old BB{seq} values can reintroduce
        // collisions with product codes, so this is not guaranteed to be
        // collision-free once new products exist.
        DB::table('product_units')
            ->select('id', 'unit_code', 'product_code')
            ->orderBy('id')
            ->chunkById(100, function ($units): void {
                foreach ($units as $unit) {
                    $serial = $this->serialFrom($unit->unit_code);
                    if ($serial <= 0) {
                        continue;
                    }

                    $code = $serial < 10 ? 'BB0' . $serial : 'BB' . $serial;

                    DB::table('product_units')->where('id', $unit->id)->update([
                        'unit_code' => $code,
                        'product_code' => $code,
                        'updated_at' => now(),
                    ]);
                }
            }, 'id');
    }

    private function formatUnitCode(int $serial): string
    {
        return self::UNIT_PREFIX . str_pad((string) $serial, self::SERIAL_LENGTH, '0', STR_PAD_LEFT);
    }

    private function serialFrom(?string $value): int
    {
        return preg_match('/(\d+)$/', (string) $value, $matches) ? (int) $matches[1] : 0;
    }
};
