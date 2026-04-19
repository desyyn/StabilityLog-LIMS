<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    protected $table = 'test_results';

    protected $fillable = [
        'stability_test_id',
        'testing_parameter_id',
        'value',
        'anomaly_flag',
    ];

    protected $casts = [
        'value' => 'double',
        'anomaly_flag' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $result) {
            $result->anomaly_flag = $result->checkAnomaly();
        });
    }

    public function stabilityTest(): BelongsTo
    {
        return $this->belongsTo(StabilityTest::class);
    }

    public function testingParameter(): BelongsTo
    {
        return $this->belongsTo(TestingParameter::class);
    }

    public function checkAnomaly(): bool
    {
        if ($this->value === null || $this->testingParameter === null) {
            return false;
        }

        if ($this->testingParameter->isOrganoleptic()) {
            return false;
        }

        if ($this->testingParameter->parameterLimit !== null) {
            return !$this->testingParameter->parameterLimit->isWithinRange($this->value);
        }

        return $this->value < $this->testingParameter->min_limit
            || $this->value > $this->testingParameter->max_limit;
    }

    public function getIsAnomalyAttribute(): bool
    {
        return $this->anomaly_flag;
    }
}