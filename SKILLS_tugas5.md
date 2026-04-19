# StabilityLog Skills

## Business Context
**StabilityLog** adalah sistem LIMS (*Laboratory Information Management System*) khusus untuk industri skincare yang dikembangkan dengan framework Laravel. Fokus utama sistem ini adalah mengelola pengujian stabilitas produk (stabilitas dipercepat dan jangka panjang) untuk memastikan kualitas formula sebelum dipasarkan. Sistem ini menjembatani peran Formulator dalam merancang pengujian dan Teknisi dalam mengeksekusi pengujian di laboratorium, dengan pengawasan dari QA dan Manajer R&D.

## Technical Standards (Laravel)
AI harus mengikuti standar pengembangan Laravel yang mengadopsi prinsip ketat agar kode maintainable dan robust:

1.  **Dependency Injection:** Gunakan Laravel Service Container dan *constructor injection* pada Controller atau Service. Hindari penggunaan Facades secara berlebihan; prioritaskan *type-hinting* pada interface.
2.  **Immutability:** Manfaatkan fitur PHP 8.2+ seperti `readonly properties` untuk Data Transfer Objects (DTO). Gunakan visibilitas `protected` atau `private` pada properti class untuk mencegah mutasi state yang tidak disengaja.
3.  **Configuration Externalization:** Seluruh kredensial, kunci API, dan parameter lingkungan wajib menggunakan file `.env` dan diakses melalui `config/*.php`. Dilarang keras melakukan *hardcode* nilai sensitif atau konfigurasi di dalam logika bisnis.
4.  **Domain Validation:** Gunakan **Laravel Form Request** untuk validasi input. Terapkan validasi domain yang ketat (contoh: pH wajib 0-14, interval pengujian harus berupa angka positif).
5.  **Package by Feature (Modular):** Organisasikan kode berdasarkan fitur/domain, bukan sekadar MVC standar. Contoh struktur: `App/Modules/Product`, `App/Modules/StabilityTest`, `App/Modules/Inventory`.
6.  **Structured Logging:** Gunakan Monolog dengan channel terpisah untuk audit dan error. Terapkan *parameterized logging* untuk memudahkan tracking tanpa melakukan konkatenasi string secara manual.
7.  **Testing Culture:** Setiap fitur wajib memiliki *Automated Test* menggunakan **Pest** atau **PHPUnit**. Gunakan *Database Transactions* atau *Testcontainers* untuk memastikan isolasi data saat testing.

## Mandatory Capabilities
Sistem wajib memiliki kemampuan berikut sesuai dengan PRD Final:

-   **Pendaftaran Sampel & Identifikasi:** Pendaftaran setiap batch produk menghasilkan **QR Code unik**. Status awal otomatis diset ke "Ready for Testing".
-   **Parameter Uji Dinamis:** Mendukung dua tipe parameter:
    -   **Numerik:** pH dan Viskositas (memerlukan batas toleransi).
    -   **Organoleptik:** Warna, Bau, Tekstur, Kejernihan (input kualitatif).
-   **Sistem Penjadwalan Fleksibel (Revisi):**
    -   **Opsi Standar:** Otomatis membuat jadwal berdasarkan interval bulan (Accelerated: 0,1,2,3,6 atau Long-Term: 0,1,2,3,6,9,12).
    -   **Opsi Custom:** User menginput interval dalam satuan **hari** (contoh: `1, 7, 30`).
    -   **Mekanisme:** Sistem menghitung tanggal konkret (`created_at` + interval) dan membuat entri baris di tabel `stability_tests`.
-   **Kontrol Toleransi & Anomali (Revisi):**
    -   Batas minimum dan maksimum **hanya berlaku untuk parameter numerik**.
    -   Sistem dilarang meminta atau menyimpan batas min/max untuk parameter organoleptik.
    -   Deteksi otomatis: Jika hasil uji numerik keluar dari range, sistem memberi flag anomali.
-   **Audit Trail:** Mencatat log aktivitas (siapa, kapan, data lama, data baru) menggunakan **Laravel Model Observers** untuk menjaga integritas data.
-   **RBAC (Role-Based Access Control):** Implementasi 5 role (Admin, Formulator, Teknisi, Manajer R&D, QA) dengan izin akses yang terisolasi.
-   **Input Hasil Uji Spesifik:** Form input menyesuaikan tipe data: angka untuk numerik, dan dropdown/radio button untuk organoleptik.

## UI Rules (Blade/Bootstrap)
-   **Blade Components:** Gunakan komponen Blade untuk elemen yang berulang (tombol, input, card) demi konsistensi UI.
-   **Validation Feedback:** Gunakan class Bootstrap `.is-invalid` dan `.invalid-feedback` untuk menampilkan error validasi secara real-time.
-   **Visual Warning:** Gunakan class `table-danger` atau text-red pada baris data yang terdeteksi sebagai **anomali** (hasil di luar batas toleransi).
-   **Audit Trail Display:** Tampilkan riwayat perubahan dalam bentuk Modal atau Collapsible section agar tidak memenuhi halaman utama.
-   **QR Code Integration:** Tampilkan QR Code secara jelas di halaman detail batch dengan opsi "Cetak Label".
-   **Dynamic Scheduling Form:** Gunakan JavaScript vanilla atau Alpine.js untuk menambah/menghapus field interval secara dinamis pada opsi Custom.
-   **Conditional Rendering:** Di form registrasi parameter, input "Min Value" dan "Max Value" harus disembunyikan (hidden/disabled) jika parameter yang dipilih adalah tipe **Organoleptik**.
