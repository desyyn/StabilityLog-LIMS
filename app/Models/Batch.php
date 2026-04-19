<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Batch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_code',
        'qr_code',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stabilityTests(): HasMany
    {
        return $this->hasMany(StabilityTest::class);
    }

    public function generateQRCode(): string
    {
        $directory = 'qrcodes';
        $fileName = sprintf('%s.svg', $this->batch_code);
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0755, true);
        }

        QrCode::format('svg')
            ->size(300)
            ->generate(
                $this->resolveQrUrl(),
                public_path($filePath)
            );

        $this->qr_code = $filePath;
        $this->save();

        return $filePath;
    }

    private function resolveQrUrl(): string
    {
        if ($this->batch_code) {
            return route('products.show', ['product' => $this->batch_code]);
        }

        return url('/');
    }
}
