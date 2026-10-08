<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait ValidatesTransactions
{
    /**
     * Waktu transaksi boleh diisi mundur (input susulan), tapi tidak boleh di masa depan.
     * Toleransi 5 menit untuk selisih jam HP dengan server.
     */
    protected function transactionTimeRules(): array
    {
        return [
            'nullable',
            'date_format:Y-m-d H:i:s',
            'before_or_equal:' . now()->addMinutes(5)->format('Y-m-d H:i:s'),
            'after_or_equal:2020-01-01 00:00:00',
        ];
    }

    protected function transactionTimeMessages(): array
    {
        return [
            'created_at.before_or_equal' => 'Waktu transaksi tidak boleh melebihi waktu sekarang.',
            'created_at.after_or_equal' => 'Waktu transaksi tidak valid.',
            'created_at.date_format' => 'Format waktu transaksi harus Y-m-d H:i:s.',
        ];
    }

    protected function transactionTime(Request $request): Carbon
    {
        return $request->filled('created_at')
            ? Carbon::createFromFormat('Y-m-d H:i:s', $request->created_at)
            : now();
    }

    /**
     * Aplikasi mobile mengirim daftar item sebagai string JSON (multipart form).
     * Ubah menjadi array agar bisa divalidasi per item dengan aturan Laravel biasa.
     */
    protected function decodeJsonField(Request $request, string $key): void
    {
        $value = $request->input($key);

        if (!is_string($value)) {
            return;
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([$key => "Format data {$key} tidak valid."]);
        }

        $request->merge([$key => $decoded]);
    }

    /**
     * Normalisasi angka desimal yang memakai koma (format Indonesia) menjadi titik.
     */
    protected function normalizeDecimal(Request $request, string ...$keys): void
    {
        foreach ($keys as $key) {
            $value = $request->input($key);
            if (is_string($value)) {
                $request->merge([$key => str_replace(',', '.', trim($value))]);
            }
        }
    }
}
