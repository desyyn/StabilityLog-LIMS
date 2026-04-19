<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StabilityLog - Detail Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Detail Produk</h4>
                    <p class="text-muted mb-0">Informasi lengkap pendaftaran, parameter, dan jadwal uji.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali ke Daftar Produk</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-5">Nama Produk</dt>
                            <dd class="col-7">{{ $product->name }}</dd>

                            <dt class="col-5">Kode Batch</dt>
                            <dd class="col-7">{{ $product->batch_code }}</dd>

                            <dt class="col-5">Status</dt>
                            <dd class="col-7">{{ $product->status }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="card-title">QR Code Produk</h6>
                                @if($product->qr_code)
                                    <div class="mb-3">
                                        <img src="/{{ $product->qr_code }}" alt="QR Code" class="img-fluid" style="max-width: 240px;" />
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="/{{ $product->qr_code }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka QR</a>
                                        <x-ui.button variant="outline-success" size="sm" onclick="window.print()">Cetak Label</x-ui.button>
                                    </div>
                                @else
                                    <p class="text-muted">QR Code belum tersedia.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5>Parameter Pengujian</h5>
                    @if($product->testingParameters->isNotEmpty())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Batas Min</th>
                                    <th>Batas Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->testingParameters as $parameter)
                                    <tr>
                                        <td>{{ $parameter->param_name }}</td>
                                        <td>{{ $parameter->min_limit }}</td>
                                        <td>{{ $parameter->max_limit }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Tidak ada parameter pengujian terdaftar.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <h5>Jadwal Uji Stabilitas</h5>
                    @if($product->stabilityTests->isNotEmpty())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tanggal Jadwal</th>
                                    <th>Status</th>
                                    <th>Hasil Uji</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stabilityTests as $test)
                                    <tr @class(['table-danger' => $test->testResult && $test->testResult->is_anomaly])>
                                        <td>{{ $test->schedule_date->format('Y-m-d') }}</td>
                                        <td>{{ $test->status }}</td>
                                        <td>
                                            @if($test->testResult)
                                                <span>{{ $test->testResult->value }}</span>
                                                @if($test->testResult->is_anomaly)
                                                    <span class="badge bg-danger">Anomali</span>
                                                @else
                                                    <span class="badge bg-success">Normal</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Belum diuji</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Jadwal uji belum dibuat.</p>
                    @endif
                </div>

                <div class="mb-4">
                    <h5>Audit Trail</h5>
                    <button class="btn btn-outline-secondary btn-sm mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#auditTrailCollapse" aria-expanded="false" aria-controls="auditTrailCollapse">
                        Tampilkan / Sembunyikan Audit Trail
                    </button>
                    <div class="collapse" id="auditTrailCollapse">
                        @if($product->auditTrails->isNotEmpty())
                            <ul class="list-group">
                                @foreach($product->auditTrails as $audit)
                                    <li class="list-group-item">
                                        <div class="small text-muted">{{ $audit->created_at->format('Y-m-d H:i') }}</div>
                                        <strong>{{ ucfirst($audit->event) }} {{ ucfirst(str_replace('_', ' ', $audit->auditable_type)) }} #{{ $audit->auditable_id }}</strong>
                                        <div class="small text-muted">{{ $audit->url }}</div>
                                        <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada jejak audit untuk produk ini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>