<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warga;
use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class RekapController extends Controller
{
    /*Export PDF*/
    public function exportPdf(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        $pembayarans = Pembayaran::with('warga','jenisPembayaran')
            ->when($tahun, fn ($query, $value) => $query->whereYear('tanggal_bayar', $value))
            ->when($bulan, fn ($query, $value) => $query->whereMonth('tanggal_bayar', $value))
            ->orderByDesc('tanggal_bayar')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf', compact('pembayarans', 'tahun', 'bulan'));

        return $pdf->download('laporan-pembayaran-desa.pdf');
    }
    /**
     * Rekap pembayaran per warga
     */
    public function perWarga(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $wargas = Warga::query()
            ->when($q !== '', fn ($query) => $query->where('nama', 'like', "%{$q}%"))
            ->with(['pembayarans' => function ($query) use ($status) {
                $query->with('jenisPembayaran')
                    ->when(in_array($status, ['pending', 'paid'], true), fn ($inner) => $inner->where('status', $status))
                    ->orderByDesc('tanggal_bayar');
            }])
            ->orderBy('nama')
            ->get();

        return view('rekap.per_warga', compact('wargas', 'q', 'status'));
    }

    public function exportWargaCsv(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $wargas = Warga::query()
            ->when($q !== '', fn ($query) => $query->where('nama', 'like', "%{$q}%"))
            ->with(['pembayarans' => function ($query) use ($status) {
                $query->with('jenisPembayaran')
                    ->when(in_array($status, ['pending', 'paid'], true), fn ($inner) => $inner->where('status', $status))
                    ->orderByDesc('tanggal_bayar');
            }])
            ->orderBy('nama')
            ->get();

        $rows = [];
        $no = 1;

        foreach ($wargas as $warga) {
            if ($warga->pembayarans->isEmpty()) {
                $rows[] = [
                    $no++,
                    $warga->nama,
                    '-',
                    '-',
                    '-',
                    0,
                ];

                continue;
            }

            foreach ($warga->pembayarans as $pembayaran) {
                $rows[] = [
                    $no++,
                    $warga->nama,
                    optional($pembayaran->jenisPembayaran)->nama ?? '-',
                    $pembayaran->status,
                    optional($pembayaran->tanggal_bayar)?->format('Y-m-d') ?? '-',
                    (int) $pembayaran->jumlah,
                ];
            }
        }

        return $this->streamCsv(
            'rekap-per-warga.csv',
            ['No', 'Nama Warga', 'Jenis Pembayaran', 'Status', 'Tanggal Bayar', 'Jumlah'],
            $rows
        );
    }

    /**
     * Rekap pembayaran per bulan
     */
    public function perBulan(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        $rekapBulanan = Pembayaran::select(
                DB::raw('MONTH(tanggal_bayar) as bulan'),
                DB::raw('YEAR(tanggal_bayar) as tahun'),
                DB::raw('SUM(jumlah) as total')
            )
            ->when($tahun, fn ($query, $value) => $query->whereYear('tanggal_bayar', $value))
            ->when($bulan, fn ($query, $value) => $query->whereMonth('tanggal_bayar', $value))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $tahunOptions = Pembayaran::selectRaw('YEAR(tanggal_bayar) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('rekap.per_bulan', compact('rekapBulanan', 'tahun', 'bulan', 'tahunOptions'));
    }

    public function exportBulanCsv(Request $request)
    {
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');

        $rekapBulanan = Pembayaran::select(
                DB::raw('MONTH(tanggal_bayar) as bulan'),
                DB::raw('YEAR(tanggal_bayar) as tahun'),
                DB::raw('SUM(jumlah) as total')
            )
            ->when($tahun, fn ($query, $value) => $query->whereYear('tanggal_bayar', $value))
            ->when($bulan, fn ($query, $value) => $query->whereMonth('tanggal_bayar', $value))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $rows = [];

        foreach ($rekapBulanan as $index => $item) {
            $rows[] = [
                $index + 1,
                Carbon::create()->month((int) $item->bulan)->translatedFormat('F'),
                $item->tahun,
                (int) $item->total,
            ];
        }

        return $this->streamCsv(
            'rekap-per-bulan.csv',
            ['No', 'Bulan', 'Tahun', 'Total Pembayaran'],
            $rows
        );
    }

    /**
     * Rekap tunggakan tagihan air
     */
    public function tunggakan(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $tunggakan = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->where('status', 'pending')
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<', now())
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('warga', function ($wargaQuery) use ($q) {
                    $wargaQuery->where('nama', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('jatuh_tempo')
            ->get();

        $totalTagihan = $tunggakan->count();
        $totalNominal = $tunggakan->sum('jumlah');
        $totalDenda = $tunggakan->sum('denda');

        $perWarga = $tunggakan->groupBy('warga_id')->map(function ($items) {
            $first = $items->first();

            return (object) [
                'warga' => $first->warga,
                'jumlah_tagihan' => $items->count(),
                'total' => $items->sum('jumlah'),
                'total_denda' => $items->sum('denda'),
            ];
        })->values()->sortByDesc('total');

        return view('rekap.tunggakan', compact(
            'tunggakan',
            'perWarga',
            'q',
            'totalTagihan',
            'totalNominal',
            'totalDenda'
        ));
    }

    public function exportTunggakanCsv(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $tunggakan = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->where('status', 'pending')
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<', now())
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('warga', function ($wargaQuery) use ($q) {
                    $wargaQuery->where('nama', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('jatuh_tempo')
            ->get();

        $rows = [];

        foreach ($tunggakan as $index => $item) {
            $rows[] = [
                $index + 1,
                $item->invoice ?? '-',
                optional($item->warga)->nama ?? '-',
                $item->periode ?? '-',
                optional($item->jatuh_tempo)?->format('Y-m-d') ?? '-',
                (int) $item->jumlah,
                (int) $item->denda,
            ];
        }

        return $this->streamCsv(
            'rekap-tunggakan-air.csv',
            ['No', 'Invoice', 'Nama Warga', 'Periode', 'Jatuh Tempo', 'Jumlah', 'Denda'],
            $rows
        );
    }

    private function streamCsv(string $filename, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}