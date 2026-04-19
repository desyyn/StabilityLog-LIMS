# user_story_output_tugas5.md

**Prompt:** "Generate kode lengkap Laravel (Model, Migration, Observer, Form Request, Service, Middleware RBAC, Seeder, Route, Blade View, dan Test) untuk sistem StabilityLog LIMS berdasarkan PRD/PDF terbaru dan `skills.md` terbaru. Satukan seluruh kebutuhan berikut:  
1) Core Data: `Product (id,name,description)` hasMany `Batch`; `Batch (id,product_id,batch_code,qr_code,status)` hasMany `StabilityTest` dan wajib implement `generateQRCode()`; `StabilityTest (id,batch_id,scheduled_date/ schedule_date,interval_type,interval_value,status)` hasMany `TestResult` dan wajib implement `markAsComplete()`; `TestResult (id,stability_test_id,parameter_id/testing_parameter_id,value,anomaly_flag)` belongsTo `StabilityTest` dan `Parameter` serta wajib implement `checkAnomaly()`; `Parameter (id,name,param_name,type,unit)` hasOne `ParameterLimit`; `ParameterLimit (id,parameter_id,min_value,max_value)` wajib implement `isWithinRange(value)`; `Role (id,name)` hasMany `User`; `User (id,name,role_id)` menghasilkan `AuditLog`; `AuditLog (id,user_id,table_name/ auditable_type,old_data,new_data)` dengan JSON cast.  
2) Mandatory capabilities dari `skills.md`: pendaftaran sampel menghasilkan QR unik dan status awal `Ready for Testing`; parameter dinamis numerik (pH/viskositas) + organoleptik (warna,bau,tekstur,kejernihan); jadwal fleksibel mode standar (Accelerated: 0,1,2,3,6 bulan; Long-Term: 0,1,2,3,6,9,12 bulan) dan mode custom (interval hari dinamis), sistem menghitung tanggal konkret dari `created_at`; kontrol toleransi hanya untuk numerik; auto flag anomali jika hasil numerik di luar range; audit trail via Model Observer; RBAC 5 role (Admin, Formulator, Teknisi, Manajer R&D, QA) dengan akses terisolasi; form input sesuai tipe data.  
3) Technical standards dari `skills.md`: dependency injection pada controller/service; validasi domain ketat di Form Request (contoh pH 0-14, interval positif/0, organoleptik tidak boleh min/max); modular package by feature (`App/Modules/Product`); konfigurasi sensitif via `.env`/`config`; structured logging channel terpisah (`audit` dan `error`) dengan parameterized logging; gunakan Blade components untuk elemen berulang; validation feedback Bootstrap (`is-invalid`, `invalid-feedback`); visual warning `table-danger` untuk anomali; audit trail tampil di collapse/modal; QR detail + tombol cetak label; dynamic scheduling form (add/remove interval custom); conditional rendering min/max hidden+disabled untuk organoleptik; tambahkan automated test feature untuk validasi utama.  
4) Gunakan foreign key dan tipe data yang sesuai (JSON untuk audit log, float/double untuk limit), jaga clean code, pisahkan model per file, dan pertahankan kompatibilitas field lama (`TestingParameter` sebagai alias `Parameter` bila diperlukan).  
5) Integrasikan hasil iterasi bugfix sebelumnya: organoleptik checkbox harus memunculkan respon visual, min/max tersembunyi untuk organoleptik, scheduling custom berjalan, RBAC route middleware aktif, serta siapkan mockup UI role switcher untuk demo pembuktian RBAC."

**Context File:** `skills.md`, PRD/PDF terbaru, log perubahan chat iterasi tugas 5

**Skills:** Laravel best practices + StabilityLog domain rules (`skills.md`)

**Task:** Generate code for user story "As a user, I want to register stability testing sample with dynamic parameters, flexible schedule, QR traceability, anomaly detection, audit trail, and RBAC access control."

**Input:** `@parameter array $payload` (`name`, `batch_code`, `schedule_mode`, `stability_type`, `custom_intervals[]`, `parameters[]`)

**Output:** `@return type_data return_type` `//@return Boolean true`

**Rules:**  
- Validasi wajib di Form Request (required fields, unique batch, pH 0-14, interval >= 0, numerik wajib min/max, organoleptik dilarang min/max).  
- Foreign key relasi harus valid dan konsisten antar tabel.  
- Audit log gunakan JSON cast (`old_values/old_data`, `new_values/new_data`).  
- Deteksi anomali hanya untuk parameter numerik.  
- Jalur role-protected wajib mengembalikan `403` saat role tidak sesuai.  
- Gunakan DI, modular feature structure, blade component, logging channel terpisah.

---

## What Changed

- Menambahkan model domain baru: `Batch`, `Parameter`, `ParameterLimit`, `Role`, `AuditLog`.
- Memperbarui model inti:
  - `Product`: relasi `batches`, `parameters`, `auditLogs`.
  - `StabilityTest`: `batch`, `testResults`, `testResult`, `markAsComplete`, `scheduleForBatch`.
  - `TestResult`: `anomaly_flag`, `checkAnomaly`, accessor `is_anomaly`.
  - `TestingParameter`: alias ke `Parameter`.
  - `AuditTrail`: accessor alias `table_name`, `old_data`, `new_data`.
  - `User`: relasi `role`, dukungan `role_id`.
- Menambahkan migrasi domain baru:
  - `create_batches_table`
  - `create_roles_table`
  - `add_role_id_to_users_table`
  - `create_parameter_limits_table`
  - `add_type_unit_to_testing_parameters_table`
  - `add_anomaly_flag_to_test_results_table`
  - `add_batch_and_interval_fields_to_stability_tests_table`
- Memperbarui `RegisterProductAction`:
  - buat `Product` + `Batch`
  - generate QR code via `Batch::generateQRCode()`
  - simpan parameter numerik/organoleptik
  - buat jadwal standar/custom.
- Memperbarui `StoreProductRequest`:
  - validasi mode penjadwalan standar/custom
  - validasi domain parameter numerik vs organoleptik
  - validasi interval custom.
- Memperbarui UI `create.blade.php`:
  - dynamic scheduling form (add/remove interval custom)
  - conditional rendering min/max untuk organoleptik
  - feedback validasi Bootstrap.
- Menambahkan structured logging channel `audit` dan `error`.
- Menambahkan observer logging audit (`ProductObserver`, `TestResultObserver`).
- Menambahkan middleware RBAC `EnsureUserHasRole`, alias middleware, dan proteksi route berbasis role.
- Menambahkan seeder role 5 level (`RoleSeeder`) dan pemanggilan di `DatabaseSeeder`.
- Menambahkan mockup pembuktian RBAC di halaman daftar produk:
  - panel role aktif simulasi
  - tombol `Ganti Role (Mockup)`
  - penjelasan hak akses tiap role.
- Menambahkan test fitur validasi: `ProductRegistrationValidationTest`.

**Commit Message:** `feat: align StabilityLog domain, RBAC, scheduling, and validation with latest skills standards`
