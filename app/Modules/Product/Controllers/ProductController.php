<?php

namespace App\Modules\Product\Controllers;

use App\Models\Product;
use App\Modules\Product\Requests\StoreProductRequest;
use App\Modules\Product\Services\RegisterProductAction;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController
{
    private array $availableParameters = [
        ['key' => 'ph', 'label' => 'pH', 'type' => 'numeric', 'unit' => 'pH', 'hint' => 'Rentang 0.0 - 14.0'],
        ['key' => 'viscosity', 'label' => 'Viskositas', 'type' => 'numeric', 'unit' => 'cP', 'hint' => 'Masukkan batas min/max dalam cP'],
        ['key' => 'color', 'label' => 'Warna', 'type' => 'organoleptic', 'unit' => null, 'hint' => 'Penilaian kualitatif warna sampel.'],
        ['key' => 'odor', 'label' => 'Bau', 'type' => 'organoleptic', 'unit' => null, 'hint' => 'Penilaian kualitatif aroma sampel.'],
        ['key' => 'texture', 'label' => 'Tekstur', 'type' => 'organoleptic', 'unit' => null, 'hint' => 'Penilaian kualitatif tekstur sampel.'],
        ['key' => 'clarity', 'label' => 'Kejernihan', 'type' => 'organoleptic', 'unit' => null, 'hint' => 'Penilaian kualitatif kejernihan sampel.'],
    ];

    /**
     * Menampilkan daftar semua produk dengan jadwal uji stabilitas
     */
    public function index(): View
    {
        $products = Product::with(['stabilityTests.testResult', 'testingParameters'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('modules.product.index', compact('products'));
    }

    /**
     * Menampilkan form pendaftaran sampel baru
     */
    public function create(): View
    {
        $availableParameters = $this->availableParameters;

        return view('modules.product.create', compact('availableParameters'));
    }

    /**
     * Menyimpan dan mendaftarkan sampel produk baru
     */
    public function store(StoreProductRequest $request, RegisterProductAction $action): RedirectResponse
    {
        try {
            $action->execute($request->validated());

            return redirect()->route('products.index')
                ->with('success', 'Sampel berhasil didaftarkan dan jadwal uji stabilitas telah dibuat.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal mendaftarkan sampel. Silahkan coba lagi. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Menampilkan detail produk dengan jadwal uji dan parameter
     */
    public function show(Product $product): View
    {
        $product->load(['stabilityTests.testResult.testingParameter', 'testingParameters', 'auditTrails']);

        return view('modules.product.show', compact('product'));
    }

    /**
     * Menghapus produk dan data testnya
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Sampel berhasil dihapus.');
    }
}