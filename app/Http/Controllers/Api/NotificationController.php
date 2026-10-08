<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockService;

class NotificationController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    /**
     * Peringatan limbah B3 yang masih tersimpan dan mendekati / melewati batas masa simpan.
     */
    public function getWarnings()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data peringatan berhasil dimuat',
            'data' => $this->stock->b3Alerts(),
        ], 200);
    }
}
