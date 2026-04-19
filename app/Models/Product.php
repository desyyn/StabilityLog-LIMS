<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'batch_code',
        'qr_code',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function stabilityTests(): HasMany
    {
        return $this->hasMany(StabilityTest::class);
    }

    public function testingParameters(): HasMany
    {
        return $this->hasMany(TestingParameter::class);
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(Parameter::class);
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'auditable_id')->where('auditable_type', 'product');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'auditable_id')->where('auditable_type', 'product');
    }
}
