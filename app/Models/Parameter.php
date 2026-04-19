<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Parameter extends Model
{
    protected $table = 'testing_parameters';

    protected $fillable = [
        'product_id',
        'name',
        'param_name',
        'type',
        'unit',
        'min_limit',
        'max_limit',
    ];

    protected $casts = [
        'min_limit' => 'double',
        'max_limit' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function parameterLimit(): HasOne
    {
        return $this->hasOne(ParameterLimit::class, 'parameter_id');
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class, 'testing_parameter_id');
    }

    public function isNumeric(): bool
    {
        return strtolower($this->type) === 'numeric';
    }

    public function isOrganoleptic(): bool
    {
        return strtolower($this->type) === 'organoleptic';
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? $this->attributes['param_name'] ?? '';
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['param_name'] = $value;
    }
}
