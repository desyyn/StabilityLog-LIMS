<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StabilityTest extends Model
{
    protected $fillable = [
        'batch_id',
        'product_id',
        'schedule_date',
        'interval_type',
        'interval_value',
        'status',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'interval_value' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function testResult(): HasOne
    {
        return $this->hasOne(TestResult::class);
    }

    public function markAsComplete(): bool
    {
        $this->status = 'Completed';

        return $this->save();
    }

    public static function scheduleForBatch(Batch $batch, string $intervalType, array $intervalValues): array
    {
        $scheduled = [];
        $baseDate = $batch->created_at ? $batch->created_at->copy() : Carbon::now();

        foreach ($intervalValues as $intervalValue) {
            $scheduleDate = $baseDate->copy();

            if ($intervalType === 'months') {
                $scheduleDate = $scheduleDate->addMonths($intervalValue);
            } else {
                $scheduleDate = $scheduleDate->addDays($intervalValue);
            }

            $scheduled[] = self::create([
                'batch_id' => $batch->id,
                'product_id' => $batch->product_id,
                'schedule_date' => $scheduleDate->toDateString(),
                'interval_type' => $intervalType,
                'interval_value' => $intervalValue,
                'status' => 'Scheduled',
            ]);
        }

        return $scheduled;
    }
}
