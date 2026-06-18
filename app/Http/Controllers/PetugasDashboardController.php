<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Warga;

class PetugasDashboardController extends Controller
{
    public function index()
    {
        $totalWarga = Warga::count();
        $totalPembayaran = Pembayaran::count();
        $totalLunas = Pembayaran::where('status', 'paid')->count();
        $totalPending = Pembayaran::where('status', 'pending')->count();
        $totalPemasukan = (int) Pembayaran::where('status', 'paid')->sum('jumlah');

        $pembayaranTerbaru = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->latest('tanggal_bayar')
            ->limit(5)
            ->get();

        return view('petugas.dashboard', compact(
            'totalWarga',
            'totalPembayaran',
            'totalLunas',
            'totalPending',
            'totalPemasukan',
            'pembayaranTerbaru'
        ));
    }
}