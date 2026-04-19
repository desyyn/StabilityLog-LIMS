<?php

namespace App\Modules\Product\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StabilityTest;
use App\Models\TestingParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterProductAction
{
    private const ACCELERATED_INTERVALS = [0, 1, 2, 3, 6];
    private const LONG_TERM_INTERVALS = [0, 1, 2, 3, 6, 9, 12];

    /**
     * Eksekusi pendaftaran produk dengan otomatisasi jadwal dan QR generation
     *
     * @param array $data Data produk (name, batch_code, parameters)
     * @return bool True jika berhasil
     */
    public function execute(array $data): bool
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Buat Product master data yang tetap kompatibel dengan view lama
                $product = Product::create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'batch_code' => $data['batch_code'],
                    'status' => 'Ready',
                ]);

                // 2. Buat batch terkait dan generate QR Code unik
                $batch = Batch::create([
                    'product_id' => $product->id,
                    'batch_code' => $data['batch_code'],
                    'status' => 'Ready for Testing',
                ]);

                $qrPath = $batch->generateQRCode();
                $product->update(['qr_code' => $qrPath]);

                // 3. Simpan parameter pengujian yang dipilih
                foreach ($data['parameters'] as $parameterKey => $parameterData) {
                    if (empty($parameterData['enabled'])) {
                        continue;
                    }

                    TestingParameter::create([
                        'product_id' => $product->id,
                        'param_name' => $parameterData['param_name'],
                        'type' => $parameterData['type'] ?? 'numeric',
                        'unit' => $parameterData['unit'] ?? null,
                        'min_limit' => $parameterData['type'] === 'organoleptic' ? null : $parameterData['min_limit'],
                        'max_limit' => $parameterData['type'] === 'organoleptic' ? null : $parameterData['max_limit'],
                    ]);
                }

                // 4. Otomatisasi jadwal berdasarkan mode standar/custom
                [$intervalType, $scheduleIntervals] = $this->resolveSchedule($data);

                StabilityTest::scheduleForBatch($batch, $intervalType, $scheduleIntervals);

                Log::channel('audit')->info('Product registered successfully', [
                    'product_id' => $product->id,
                    'batch_code' => $data['batch_code'],
                    'schedules_created' => count($scheduleIntervals),
                    'interval_type' => $intervalType,
                    'interval_values' => $scheduleIntervals,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::channel('error')->error('Product registration failed', [
                'batch_code' => $data['batch_code'],
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function resolveSchedule(array $data): array
    {
        if (($data['schedule_mode'] ?? null) === 'custom') {
            $customIntervals = collect($data['custom_intervals'] ?? [])
                ->map(static fn ($value) => (int) $value)
                ->filter(static fn ($value) => $value >= 0)
                ->unique()
                ->sort()
                ->values()
                ->all();

            return ['days', $customIntervals];
        }

        $stabilityType = $data['stability_type'] ?? 'accelerated';

        if ($stabilityType === 'long_term') {
            return ['months', self::LONG_TERM_INTERVALS];
        }

        return ['months', self::ACCELERATED_INTERVALS];
    }
}