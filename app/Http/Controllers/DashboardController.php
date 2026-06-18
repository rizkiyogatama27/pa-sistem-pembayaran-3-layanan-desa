<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use App\Models\JenisPembayaran;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    public function index()
    {
        $totalWarga = Warga::count();
        $totalJenis = JenisPembayaran::count();
        $totalPembayaran = Pembayaran::count();
        $totalUang = Pembayaran::sum('jumlah');

        return view('dashboard', compact(
            'totalWarga',
            'totalJenis',
            'totalPembayaran',
            'totalUang'
        ));
    }
}