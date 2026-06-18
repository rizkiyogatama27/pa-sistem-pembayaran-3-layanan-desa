<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Donasi</title>

@php
    $paymentDriver = config('services.payment_gateway.driver', 'midtrans');
    $isProduction = (bool) config('services.midtrans.is_production');
    $snapUrl = $isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

@if($paymentDriver === 'midtrans')
<script src="{{ $snapUrl }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
@endif

</head>

<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;padding:24px;">

<div id="payment-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.38);backdrop-filter:blur(2px);z-index:60;align-items:center;justify-content:center;padding:20px;">
    <div id="payment-overlay-card" style="min-width:280px;max-width:420px;width:100%;background:#fff;border-radius:16px;box-shadow:0 24px 50px rgba(15,23,42,.24);padding:18px 20px;text-align:center;border:1px solid #e2e8f0;">
        <div id="payment-overlay-spinner" style="width:42px;height:42px;margin:0 auto 12px;border:4px solid #dbeafe;border-top-color:#1d4ed8;border-radius:999px;animation:spin 0.8s linear infinite;"></div>
        <div id="payment-overlay-title" style="font-size:16px;font-weight:800;color:#18324d;">Memproses donasi...</div>
        <div id="payment-overlay-text" style="margin-top:6px;font-size:13px;line-height:1.5;color:#64748b;">Mohon tunggu sebentar.</div>
    </div>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.payment-success-card {
    border: 1px solid #86efac !important;
    background: #f0fdf4 !important;
}
.payment-success-title { color: #166534 !important; }
.payment-success-text { color: #166534 !important; }
</style>

<div style="max-width:640px;margin:auto;background:#fff;padding:28px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.08);">

<h2 style="margin-top:0;">Total Donasi: Rp {{ number_format($donationPayment->jumlah, 0, ',', '.') }}</h2>

<p style="color:#555;">Invoice: {{ $donationPayment->invoice ?? '-' }}</p>
<p style="color:#555;">Event: {{ optional($donationPayment->eventDonasi)->nama_event ?? '-' }}</p>

<p style="margin:12px 0;padding:8px;border-radius:6px;background:#f0f9ff;border:1px solid #bfdbfe;color:#1e40af;font-size:13px;">
    <strong>Gateway Aktif:</strong> {{ strtoupper($paymentDriver) }}
    @if($paymentDriver === 'pakasir')
        · Pembayaran akan diarahkan ke Pakasir.
    @else
        · Pembayaran akan menggunakan Midtrans.
    @endif
</p>

@if(session('success'))
<div style="margin:12px 0;padding:10px 12px;border-radius:8px;background:#ecfdf3;color:#166534;border:1px solid #bbf7d0;">{{ session('success') }}</div>
@endif

@if(session('error'))
<div style="margin:12px 0;padding:10px 12px;border-radius:8px;background:#fff1f2;color:#9f1239;border:1px solid #fecdd3;">{{ session('error') }}</div>
@endif

@if($paymentDriver === 'midtrans')
<button id="pay-button" style="padding:12px 18px;border:0;border-radius:8px;background:#111827;color:#fff;cursor:pointer;">Bayar Donasi</button>
@else
<p style="margin:12px 0;font-size:13px;color:#059669;background:#ecfdf5;padding:10px;border-radius:6px;border:1px solid #6ee7b7;">
    <strong>✓ Akses checkout Pakasir sudah siap.</strong> Setelah Anda klik tombol di bawah, Anda akan diarahkan ke halaman Pakasir untuk menyelesaikan pembayaran.
</p>
<a href="{{ route('dashboard') }}" style="display:inline-block;padding:12px 18px;border:0;border-radius:8px;background:#111827;color:#fff;text-decoration:none;cursor:pointer;">Lanjut ke Pembayaran</a>
@endif

<form id="cancel-form" method="POST" action="{{ route('donation-payment.cancel', $donationPayment->id) }}" style="display:inline-block;margin-left:10px;">
@csrf
<button type="submit" style="padding:12px 18px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#111827;cursor:pointer;">Batalkan Transaksi</button>
</form>

@if($paymentDriver === 'midtrans')
<p style="margin-top:10px;font-size:13px;color:#6b7280;">Silakan pilih metode pembayaran yang tersedia dan ikuti petunjuk pada layar hingga transaksi selesai.</p>
@endif

<br><br>
<a href="{{ route('user.event-donasi.show', $donationPayment->event_donasi_id) }}">Kembali</a>

@if($paymentDriver === 'midtrans')
<script>

const finishUrl = "{{ route('donation-payment.finish') }}";
const csrfToken = "{{ csrf_token() }}";
const cancelForm = document.getElementById('cancel-form');

document.getElementById('pay-button').onclick = function () {

showPaymentOverlay('Memproses donasi...', 'Mohon tunggu, sistem sedang memverifikasi transaksi Anda.');

snap.pay('{{ $snapToken }}', {

onSuccess: async function(result){
showPaymentOverlay('Donasi berhasil', 'Terima kasih, donasi Anda sedang diproses.');
console.log(result);

try {
await fetch(finishUrl, {
method: "POST",
headers: {
"Content-Type": "application/json",
"X-CSRF-TOKEN": csrfToken
},
body: JSON.stringify({
order_id: result.order_id || "{{ $donationPayment->invoice }}"
})
});
} catch (error) {
console.error("Verifikasi status gagal:", error);
}

setTimeout(function () {
    location.href = "{{ route('user.event-donasi.show', $donationPayment->event_donasi_id) }}";
}, 1700);
},

onPending: function(result){
showPaymentOverlay('Menunggu pembayaran', 'Silakan selesaikan pembayaran sesuai metode yang dipilih.');
console.log(result);

if (cancelForm) {
cancelForm.style.display = 'inline-block';
}
},

onError: function(result){
showPaymentOverlay('Pembayaran gagal', 'Silakan coba lagi atau pilih metode lain.');
console.log(result);

if (cancelForm) {
cancelForm.style.display = 'inline-block';
}
},

onClose: function(){
showPaymentOverlay('Popup ditutup', 'Jika ingin ganti metode, klik Batalkan Transaksi lalu Bayar Donasi lagi.');

if (cancelForm) {
cancelForm.style.display = 'inline-block';
}
}

});

};

function showPaymentOverlay(title, text, isSuccess = false) {
    const overlay = document.getElementById('payment-overlay');
    const card = document.getElementById('payment-overlay-card');
    const spinner = document.getElementById('payment-overlay-spinner');
    const overlayTitle = document.getElementById('payment-overlay-title');
    const overlayText = document.getElementById('payment-overlay-text');

    if (!overlay || !card || !spinner || !overlayTitle || !overlayText) {
        return;
    }

    overlay.style.display = 'flex';
    overlayTitle.textContent = title;
    overlayText.textContent = text;

    if (isSuccess || title.toLowerCase().includes('berhasil')) {
        card.classList.add('payment-success-card');
        overlayTitle.classList.add('payment-success-title');
        overlayText.classList.add('payment-success-text');
        spinner.style.display = 'none';
    } else {
        card.classList.remove('payment-success-card');
        overlayTitle.classList.remove('payment-success-title');
        overlayText.classList.remove('payment-success-text');
        spinner.style.display = 'block';
    }
}

</script>
@endif

</div>

</body>
</html>
