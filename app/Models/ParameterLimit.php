<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParameterLimit extends Model
{
    protected $fillable = [
        'parameter_id',
        'min_value',
        'max_value',
    ];

    protected $casts = [
        'min_value' => 'double',
        'max_value' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(Parameter::class);
    }

    public function isWithinRange(float|int|null $value): bool
    {
        if ($value === null) {
            return false;
        }

        if ($this->min_value !== null && $value < $this->min_value) {
            return false;
        }

        if ($this->max_value !== null && $value > $this->max_value) {
            return false;
        }

        return true;
    }
}
