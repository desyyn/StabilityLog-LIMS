<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_schedule_requires_at_least_one_interval(): void
    {
        $payload = [
            'name' => 'Serum A',
            'batch_code' => 'BATCH-VAL-001',
            'schedule_mode' => 'custom',
            'custom_intervals' => [],
            'parameters' => [
                'ph' => [
                    'enabled' => '1',
                    'param_name' => 'pH',
                    'type' => 'numeric',
                    'unit' => 'pH',
                    'min_limit' => 5.5,
                    'max_limit' => 6.5,
                ],
            ],
        ];

        $response = $this->from(route('products.create'))->post(route('products.store'), $payload);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors(['custom_intervals']);
    }

    public function test_organoleptic_parameter_rejects_min_max_limit(): void
    {
        $payload = [
            'name' => 'Serum B',
            'batch_code' => 'BATCH-VAL-002',
            'schedule_mode' => 'standard',
            'stability_type' => 'accelerated',
            'parameters' => [
                'color' => [
                    'enabled' => '1',
                    'param_name' => 'Warna',
                    'type' => 'organoleptic',
                    'unit' => null,
                    'min_limit' => 1,
                    'max_limit' => 2,
                ],
            ],
        ];

        $response = $this->from(route('products.create'))->post(route('products.store'), $payload);

        $response->assertRedirect(route('products.create'));
        $response->assertSessionHasErrors([
            'parameters.color.min_limit',
            'parameters.color.max_limit',
        ]);
    }
}
