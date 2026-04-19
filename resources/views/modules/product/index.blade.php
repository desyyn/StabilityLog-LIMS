<!DOCTYPE html>
<html>
<head>
    <title>StabilityLog - Daftar Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="alert alert-info border mb-4">
            <div class="fw-semibold mb-2">Mockup RBAC: middleware role sudah aktif.</div>
            <div class="small text-muted mb-2">
                Role aktif (simulasi UI): <span class="fw-semibold text-dark">{{ request('mock_role', 'Admin') }}</span>
            </div>
            <div class="small mb-2">
                <div class="fw-semibold mb-1">Penjelasan Role:</div>
                <ul class="mb-2 ps-3">
                    <li><strong>Admin</strong>: Akses penuh termasuk daftar, detail, registrasi sampel, dan hapus data.</li>
                    <li><strong>Formulator</strong>: Dapat registrasi sampel baru, melihat data, dan mengelola pengujian awal.</li>
                    <li><strong>Teknisi</strong>: Fokus melihat daftar/detail untuk eksekusi pengujian laboratorium.</li>
                    <li><strong>Manajer R&amp;D</strong>: Memantau hasil dan status stabilitas untuk keputusan pengembangan formula.</li>
                    <li><strong>QA</strong>: Melihat data untuk verifikasi mutu, audit, dan kepatuhan.</li>
                </ul>
            </div>
            <div class="small mb-2">Gunakan tombol di bawah untuk mengganti role saat demo.</div>
            <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2 align-items-center">
                <select name="mock_role" class="form-select form-select-sm" style="max-width: 220px;">
                    @foreach(['Admin', 'Formulator', 'Teknisi', 'Manajer R&D', 'QA'] as $role)
                        <option value="{{ $role }}" @selected(request('mock_role', 'Admin') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Ganti Role (Mockup)</button>
            </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Produk Tersimpan</h4>
                <p class="text-muted mb-0">Menampilkan produk yang sudah didaftarkan beserta jadwal uji otomatis.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary">Tambah Sampel Baru</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <x-ui.card class="shadow-sm">
            <div class="p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Produk</th>
                            <th>Kode Batch</th>
                            <th>Status</th>
                            <th>Jadwal Uji</th>
                            <th>QR Code</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr @class(['table-danger' => $product->stabilityTests->contains(function ($test) { return optional($test->testResult)->is_anomaly; })])>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->batch_code }}</td>
                                <td>{{ $product->status }}</td>
                                <td>
                                    @if($product->stabilityTests->isNotEmpty())
                                        @foreach($product->stabilityTests as $test)
                                            <div>{{ $test->schedule_date }} <small class="text-muted">({{ ucfirst(strtolower($test->status)) }})</small></div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($product->qr_code)
                                        <a href="/{{ $product->qr_code }}" target="_blank">Lihat QR</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">Lihat</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus sampel ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="outline-danger" size="sm">Hapus</x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Belum ada produk terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</body>
</html>