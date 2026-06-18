@extends('layouts.app')

@section('content')


<div class="section">
    <div class="section-label">Panduan</div>
    <h2 class="section-title">Tata Cara Pembayaran</h2>
    <div class="panel">
        <ol class="payment-steps" style="margin:0;padding-left:1.2rem;line-height:1.8;">
            <li><strong>Masuk / Daftar:</strong> Klik "Cek Tagihan Saya" lalu masuk menggunakan akun Anda. Jika belum punya akun, daftar terlebih dahulu.</li>
            <li><strong>Periksa Tagihan:</strong> Setelah masuk, buka halaman Dashboard atau Tagihan untuk melihat daftar tagihan yang harus dibayar.</li>
            <li><strong>Pilih Tagihan:</strong> Pilih tagihan yang ingin dibayar lalu klik tombol <em>Bayar</em> pada baris tagihan tersebut.</li>
            <li><strong>Pilih Metode Pembayaran:</strong> Pilih metode pembayaran yang tersedia. Sistem akan mengarahkan Anda ke halaman pembayaran penyedia (Pakasir) jika diperlukan.</li>
            <li><strong>Selesaikan Pembayaran:</strong> Ikuti instruksi penyedia pembayaran: scan QRIS atau lakukan transfer ke Virtual Account sesuai instruksi.</li>
            <li><strong>Tunggu Konfirmasi:</strong> Setelah melakukan pembayaran, status akan diperbarui otomatis (jika webhook aktif) atau Anda dapat kembali ke halaman Tagihan untuk memastikan status telah berubah menjadi <em>Lunas</em>.</li>
            <li><strong>Masalah / Bantuan:</strong> Jika pembayaran belum tercatat setelah 30 menit, hubungi petugas desa atau admin melalui menu Kontak.</li>
        </ol>
    </div>
</div>

@endsection