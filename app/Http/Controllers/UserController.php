<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $tagihan = Pembayaran::where('warga_id', $user->warga_id)
                    ->where('status', 'pending')
                    ->get();

        return view('user.dashboard', compact('tagihan'));
    }

    public function riwayat()
    {
        $user = Auth::user();

        $riwayat = Pembayaran::where('warga_id', $user->warga_id)
                    ->where('status', 'paid')
                    ->get();

        return view('user.riwayat', compact('riwayat'));
    }
}