<?php

namespace App\Observers;

use App\Models\AuditTrail;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ProductObserver
{
    public function created(Product $product): void
    {
        $payload = [
            'user_id' => Auth::id(),
            'auditable_type' => 'product',
            'auditable_id' => $product->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => $product->getAttributes(),
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        AuditTrail::create($payload);
        Log::channel('audit')->info('Product created observed', $payload);
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();

        if (empty($changes)) {
            return;
        }

        $payload = [
            'user_id' => Auth::id(),
            'auditable_type' => 'product',
            'auditable_id' => $product->id,
            'event' => 'updated',
            'old_values' => $product->getOriginal(),
            'new_values' => $product->getAttributes(),
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        AuditTrail::create($payload);
        Log::channel('audit')->info('Product updated observed', $payload);
    }

    public function deleted(Product $product): void
    {
        $payload = [
            'user_id' => Auth::id(),
            'auditable_type' => 'product',
            'auditable_id' => $product->id,
            'event' => 'deleted',
            'old_values' => $product->getOriginal(),
            'new_values' => null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        AuditTrail::create($payload);
        Log::channel('audit')->info('Product deleted observed', $payload);
    }
}
