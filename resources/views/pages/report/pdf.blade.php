<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengelolaan Sampah - WasteTracking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #fff; color: #1f2937; }

        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .page-break { page-break-before: always; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }

        .header-gradient {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
        }
        .stat-box {
            border: 1px solid #d1fae5;
            border-radius: 8px;
            background: #f0fdf4;
        }
        table { border-collapse: collapse; width: 100%; }
        th { background: #f9fafb; font-size: 10px; font-weight: 700; color: #6b7280;
             text-transform: uppercase; letter-spacing: 0.05em; padding: 8px 12px; text-align: left;
             border-bottom: 1px solid #e5e7eb; }
        td { padding: 8px 12px; font-size: 11px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tr:hover { background: #f9fafb; }
        tfoot td { background: #f0fdf4 !important; font-weight: 700; color: #065f46; border-top: 2px solid #d1fae5; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; }
        .section-title { font-size: 14px; font-weight: 800; color: #065f46; border-left: 4px solid #10b981; padding-left: 10px; margin-bottom: 12px; }
        .watermark { position: fixed; bottom: 30px; right: 30px; opacity: 0.06; font-size: 60px; font-weight: 900; color: #064e3b; transform: rotate(-20deg); pointer-events: none; }
    </style>
</head>
<body class="p-0 m-0">

    <div class="watermark">WASTETRACKING</div>

    {{-- PRINT BUTTON --}}
    <div class="no-print fixed top-4 right-4 flex gap-2 z-50">
        <button onclick="window.print()"
            class="flex items-center gap-2 bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg hover:bg-emerald-700 transition-colors">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close()"
            class="bg-white border border-gray-200 text-gray-600 text-sm font-medium px-4 py-2.5 rounded-xl shadow hover:bg-gray-50 transition-colors">
            ✕ Tutup
        </button>
    </div>

    <div class="max-w-5xl mx-auto py-10 px-8">

        {{-- ====== KOVERTER HALAMAN LAPORAN ====== --}}
        <div class="header-gradient rounded-2xl p-8 text-white mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-emerald-200 text-xs font-semibold uppercase tracking-widest mb-2">Sistem Monitoring Rumah Sampah</p>
                    <h1 class="text-3xl font-extrabold tracking-tight mb-1">Laporan Pengelolaan Sampah</h1>
                    <p class="text-emerald-100 text-sm">WasteTracking — Politeknik Negeri Batam</p>
                </div>
                <div class="text-right text-sm">
                    <p class="text-emerald-200 text-xs mb-1">Periode Laporan</p>
                    <p class="font-bold text-lg">{{ $dateFrom->format('d M Y') }}</p>
                    <p class="text-emerald-200 text-xs my-0.5">s/d</p>
                    <p class="font-bold text-lg">{{ $dateTo->format('d M Y') }}</p>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-emerald-700/50 flex flex-wrap gap-4 text-xs text-emerald-200">
                <span>📅 Dicetak: {{ now()->format('d M Y, H:i') }} WIB</span>
                <span>📄 Dokumen Resmi Instansi</span>
            </div>
        </div>

        {{-- RINGKASAN EKSEKUTIF --}}
        <div class="mb-8">
            <div class="section-title">Ringkasan Eksekutif</div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="stat-box p-4">
                    <p class="text-xs text-gray-500 mb-1">Total Sampah Masuk</p>
                    <p class="text-2xl font-extrabold text-emerald-700">{{ number_format($stats['total_waste_in'],2,',','.') }}</p>
                    <p class="text-xs text-gray-400">Kilogram (Kg)</p>
                </div>
                <div class="stat-box p-4" style="background:#eff6ff;border-color:#bfdbfe;">
                    <p class="text-xs text-gray-500 mb-1">Total Sampah Keluar</p>
                    <p class="text-2xl font-extrabold text-blue-700">{{ number_format($stats['total_waste_out'],2,',','.') }}</p>
                    <p class="text-xs text-gray-400">Kilogram (Kg)</p>
                </div>
                <div class="stat-box p-4" style="background:#f5f3ff;border-color:#ddd6fe;">
                    <p class="text-xs text-gray-500 mb-1">Total Sampah Diolah</p>
                    <p class="text-2xl font-extrabold text-violet-700">{{ number_format($stats['total_processed'],2,',','.') }}</p>
                    <p class="text-xs text-gray-400">Kg / Unit Produk</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Total Pendapatan (Penjualan)</p>
                    <p class="text-xl font-extrabold text-emerald-700">Rp {{ number_format($stats['total_revenue'],0,',','.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Jumlah Transaksi Masuk</p>
                    <p class="text-xl font-extrabold text-gray-800">{{ number_format($stats['total_entries']) }} trx</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">Jumlah Transaksi Keluar</p>
                    <p class="text-xl font-extrabold text-gray-800">{{ number_format($stats['total_out_records']) }} trx</p>
                </div>
            </div>
        </div>

        {{-- REKAPITULASI PER KATEGORI --}}
        @if($wasteByCategory->count())
        <div class="mb-8">
            <div class="section-title">Rekapitulasi Sampah Masuk per Sub-Kategori</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Kategori</th>
                        <th>Sub-Kategori</th>
                        <th class="text-right" style="width:100px">Jumlah</th>
                        <th class="text-right" style="width:80px">Transaksi</th>
                        <th class="text-right" style="width:80px">% Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wasteByCategory as $i => $cat)
                    <tr>
                        <td class="text-gray-400">{{ $i+1 }}</td>
                        <td>
                            <span class="badge" style="background:#dbeafe;color:#1d4ed8;">{{ $cat->subCategory?->category?->name ?? '-' }}</span>
                        </td>
                        <td class="font-semibold text-gray-800">{{ $cat->subCategory?->name ?? '-' }}</td>
                        <td class="text-right font-bold text-emerald-700">
                            {{ number_format(floatval($cat->total),2,',','.') }}
                            {{ $cat->subCategory?->unitMeasured?->symbol ?? 'Kg' }}
                        </td>
                        <td class="text-right text-gray-500">{{ $cat->count }}</td>
                        <td class="text-right text-gray-500">
                            {{ $stats['total_waste_in'] > 0 ? number_format(($cat->total/$stats['total_waste_in'])*100,1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">TOTAL KESELURUHAN</td>
                        <td class="text-right">{{ number_format($stats['total_waste_in'],2,',','.') }} Kg</td>
                        <td class="text-right">{{ $stats['total_entries'] }}</td>
                        <td class="text-right">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- ====== PAGE BREAK ====== --}}
        <div class="page-break"></div>

        {{-- DETAIL SAMPAH MASUK --}}
        <div class="mb-8">
            <div class="section-title">A. Detail Data Sampah Masuk (Waste Entry)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th style="width:100px">Tanggal</th>
                        <th>PIC</th>
                        <th>Kategori / Sub-Kategori</th>
                        <th class="text-right" style="width:90px">Jumlah</th>
                        <th>Lokasi Sumber</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wasteEntries as $i => $entry)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->created_at)->format('d/m/Y') }}<br><span class="text-gray-400">{{ \Carbon\Carbon::parse($entry->created_at)->format('H:i') }}</span></td>
                        <td class="font-medium text-xs">{{ $entry->user?->picDetail?->full_name ?? $entry->user?->email ?? '-' }}</td>
                        <td class="text-xs">
                            <span class="text-gray-400">{{ $entry->subCategory?->category?->name ?? '-' }}</span><br>
                            <span class="font-semibold">{{ $entry->subCategory?->name ?? '-' }}</span>
                        </td>
                        <td class="text-right font-bold text-emerald-700 text-xs whitespace-nowrap">
                            {{ number_format(floatval($entry->measured_qty),2,',','.') }}
                            {{ $entry->subCategory?->unitMeasured?->symbol ?? 'Kg' }}
                        </td>
                        <td class="text-xs text-gray-500">{{ $entry->sourceLocation?->name ?? '-' }}</td>
                        <td class="text-xs text-gray-400" style="max-width:100px;word-break:break-word;">{{ $entry->notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-gray-400 py-6">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                @if($wasteEntries->count())
                <tfoot>
                    <tr>
                        <td colspan="4">TOTAL SAMPAH MASUK</td>
                        <td class="text-right">{{ number_format($wasteEntries->sum('measured_qty'),2,',','.') }} Kg</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- ====== PAGE BREAK ====== --}}
        <div class="page-break"></div>

        {{-- DETAIL SAMPAH KELUAR --}}
        <div class="mb-8">
            <div class="section-title">B. Detail Data Sampah Keluar (Waste Out)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th style="width:100px">Tanggal</th>
                        <th>PIC</th>
                        <th>Metode</th>
                        <th>Tujuan</th>
                        <th>Item Sampah</th>
                        <th class="text-right" style="width:90px">Jumlah</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $row = 1; @endphp
                    @forelse($wasteOutRecords as $out)
                        @foreach($out->dataWasteOut as $detail)
                        <tr>
                            <td class="text-gray-400 text-xs">{{ $row++ }}</td>
                            <td class="text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($out->created_at)->format('d/m/Y') }}<br><span class="text-gray-400">{{ \Carbon\Carbon::parse($out->created_at)->format('H:i') }}</span></td>
                            <td class="font-medium text-xs">{{ $out->user?->picDetail?->full_name ?? $out->user?->email ?? '-' }}</td>
                            <td class="text-xs"><span class="badge" style="background:#fef3c7;color:#92400e;">{{ $out->wasteOutMethod?->name ?? '-' }}</span></td>
                            <td class="text-xs text-gray-600">{{ $out->wasteDestination?->name ?? '-' }}</td>
                            <td class="text-xs font-semibold">{{ $detail->wasteSubCategory?->name ?? ($detail->processedWaste?->name ?? '-') }}</td>
                            <td class="text-right font-bold text-blue-700 text-xs whitespace-nowrap">
                                {{ number_format(floatval($detail->measured_qty),2,',','.') }}
                                {{ $detail->wasteSubCategory?->unitMeasured?->symbol ?? 'Kg' }}
                            </td>
                            <td class="text-xs text-gray-400" style="max-width:80px;word-break:break-word;">{{ $out->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400 py-6">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- DETAIL SAMPAH DIOLAH --}}
        @if($processedWasteRecords->count())
        <div class="mb-8">
            <div class="section-title">C. Detail Sampah Diolah (Processed Waste)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th style="width:100px">Tanggal</th>
                        <th>PIC</th>
                        <th>Produk Olahan</th>
                        <th class="text-right" style="width:90px">Jumlah</th>
                        <th>Satuan</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processedWasteRecords as $i => $proc)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($proc->created_at)->format('d/m/Y') }}<br><span class="text-gray-400">{{ \Carbon\Carbon::parse($proc->created_at)->format('H:i') }}</span></td>
                        <td class="font-medium text-xs">{{ $proc->user?->picDetail?->full_name ?? $proc->user?->email ?? '-' }}</td>
                        <td class="text-xs"><span class="badge" style="background:#ede9fe;color:#6d28d9;">{{ $proc->processedWaste?->name ?? '-' }}</span></td>
                        <td class="text-right font-bold text-violet-700 text-xs">{{ number_format(floatval($proc->measured_qty),2,',','.') }}</td>
                        <td class="text-xs text-gray-500">{{ $proc->processedWaste?->unitMeasured?->symbol ?? 'Kg' }}</td>
                        <td class="text-xs text-gray-400" style="max-width:100px;word-break:break-word;">{{ $proc->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">TOTAL SAMPAH DIOLAH</td>
                        <td class="text-right">{{ number_format($processedWasteRecords->sum('measured_qty'),2,',','.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- TANDA TANGAN --}}
        <div class="mt-12 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <div>
                    <p class="text-gray-500 mb-16">Dibuat Oleh,</p>
                    <div class="border-b border-gray-400 mb-1"></div>
                    <p class="font-semibold text-gray-700">Petugas / Operator</p>
                    <p class="text-gray-400">PIC Rumah Sampah</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-16">Diperiksa Oleh,</p>
                    <div class="border-b border-gray-400 mb-1"></div>
                    <p class="font-semibold text-gray-700">Supervisor</p>
                    <p class="text-gray-400">Kepala Bagian</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-16">Disetujui Oleh,</p>
                    <div class="border-b border-gray-400 mb-1"></div>
                    <p class="font-semibold text-gray-700">Kepala Instansi</p>
                    <p class="text-gray-400">Pejabat Berwenang</p>
                </div>
            </div>
            <div class="mt-8 text-center text-[10px] text-gray-300">
                Dokumen ini digenerate secara otomatis oleh sistem WasteTracking pada {{ now()->format('d M Y H:i') }} WIB.
                Laporan berlaku untuk periode {{ $dateFrom->format('d M Y') }} s/d {{ $dateTo->format('d M Y') }}.
            </div>
        </div>

    </div>
</body>
</html>
