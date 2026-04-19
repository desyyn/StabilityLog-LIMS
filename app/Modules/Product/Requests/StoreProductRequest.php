<?php

namespace App\Modules\Product\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreProductRequest extends FormRequest
{
    /**
     * Tentukan apakah user ini authorized untuk membuat request ini
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return true;
        }

        $roleName = strtolower((string) optional($user->role)->name);
        $legacyRole = strtolower((string) ($user->role ?? ''));

        return in_array($roleName ?: $legacyRole, ['admin', 'formulator'], true);
    }

    /**
     * Validasi rules untuk pendaftaran sampel
     * Sesuai Mandatory Capabilities dari skills.md
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'batch_code' => 'required|string|max:100|unique:products,batch_code',
            'schedule_mode' => 'required|string|in:standard,custom',
            'stability_type' => 'required_if:schedule_mode,standard|nullable|string|in:accelerated,long_term',
            'custom_intervals' => 'required_if:schedule_mode,custom|array|min:1',
            'custom_intervals.*' => 'required_if:schedule_mode,custom|integer|min:0',
            'parameters' => 'required|array|min:1',
            'parameters.*.enabled' => 'sometimes|boolean',
            'parameters.*.param_name' => 'required|string',
            'parameters.*.type' => 'required|string|in:numeric,organoleptic',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.min_limit' => 'nullable|numeric',
            'parameters.*.max_limit' => 'nullable|numeric',
        ];
    }

    /**
     * Custom messages untuk validasi
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            'batch_code.required' => 'Kode batch wajib diisi.',
            'batch_code.unique' => 'Kode batch sudah terdaftar. Gunakan kode yang berbeda.',
            'batch_code.max' => 'Kode batch maksimal 100 karakter.',
            'schedule_mode.required' => 'Mode penjadwalan wajib dipilih.',
            'schedule_mode.in' => 'Mode penjadwalan tidak valid.',
            'stability_type.required_if' => 'Jenis stabilitas wajib dipilih untuk jadwal standar.',
            'stability_type.in' => 'Jenis stabilitas harus Accelerated atau Long-Term.',
            'custom_intervals.required_if' => 'Interval custom wajib diisi untuk jadwal custom.',
            'custom_intervals.array' => 'Interval custom harus berupa daftar angka hari.',
            'custom_intervals.min' => 'Minimal satu interval custom harus diisi.',
            'custom_intervals.*.integer' => 'Interval custom harus berupa bilangan bulat.',
            'custom_intervals.*.min' => 'Interval custom harus angka positif atau nol.',
            'parameters.required' => 'Setidaknya satu parameter pengujian harus dipilih.',
            'parameters.array' => 'Daftar parameter tidak valid.',
            'parameters.min' => 'Setidaknya satu parameter pengujian harus dipilih.',
            'parameters.*.param_name.required' => 'Nama parameter diperlukan.',
            'parameters.*.min_limit.required_with' => 'Batas minimum diperlukan ketika parameter dipilih.',
            'parameters.*.max_limit.required_with' => 'Batas maksimum diperlukan ketika parameter dipilih.',
            'parameters.*.min_limit.numeric' => 'Batas minimum harus berupa angka.',
            'parameters.*.max_limit.numeric' => 'Batas maksimum harus berupa angka.',
            'parameters.*.max_limit.gte' => 'Batas maksimum harus lebih besar atau sama dengan batas minimum.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $parameters = $this->input('parameters', []);

            foreach ($parameters as $index => $parameter) {
                if (empty(Arr::get($parameter, 'enabled'))) {
                    continue;
                }

                $paramName = Arr::get($parameter, 'param_name');
                $minLimit = Arr::get($parameter, 'min_limit');
                $maxLimit = Arr::get($parameter, 'max_limit');

                $parameterType = Arr::get($parameter, 'type', 'numeric');

                if ($paramName === 'pH') {
                    if ($minLimit !== null && ($minLimit < 0.0 || $minLimit > 14.0)) {
                        $validator->errors()->add("parameters.{$index}.min_limit", 'Nilai pH minimum harus berada di antara 0.0 dan 14.0.');
                    }
                    if ($maxLimit !== null && ($maxLimit < 0.0 || $maxLimit > 14.0)) {
                        $validator->errors()->add("parameters.{$index}.max_limit", 'Nilai pH maksimum harus berada di antara 0.0 dan 14.0.');
                    }
                }

                if ($parameterType === 'numeric') {
                    if ($minLimit === null) {
                        $validator->errors()->add("parameters.{$index}.min_limit", 'Batas minimum diperlukan untuk parameter numerik.');
                    }

                    if ($maxLimit === null) {
                        $validator->errors()->add("parameters.{$index}.max_limit", 'Batas maksimum diperlukan untuk parameter numerik.');
                    }
                }

                if ($parameterType === 'organoleptic') {
                    if ($minLimit !== null || $maxLimit !== null) {
                        $validator->errors()->add("parameters.{$index}.min_limit", 'Parameter organoleptik tidak boleh menyertakan batas minimum atau maksimum.');
                        $validator->errors()->add("parameters.{$index}.max_limit", 'Parameter organoleptik tidak boleh menyertakan batas minimum atau maksimum.');
                    }
                }

                if ($minLimit !== null && $maxLimit !== null && $maxLimit < $minLimit) {
                    $validator->errors()->add("parameters.{$index}.max_limit", 'Batas maksimum harus lebih besar atau sama dengan batas minimum.');
                }
            }

            $selectedParameters = collect($parameters)->filter(fn ($parameter) => !empty(Arr::get($parameter, 'enabled')));

            if ($selectedParameters->isEmpty()) {
                $validator->errors()->add('parameters', 'Setidaknya satu parameter pengujian harus dipilih.');
            }

            if ($this->input('schedule_mode') === 'custom') {
                $intervals = collect($this->input('custom_intervals', []))
                    ->filter(fn ($value) => $value !== null && $value !== '');

                if ($intervals->isEmpty()) {
                    $validator->errors()->add('custom_intervals', 'Interval custom wajib diisi minimal satu nilai.');
                }
            }
        });
    }
}