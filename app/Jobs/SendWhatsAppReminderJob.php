<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\WhatsAppService;
use App\Models\Pembayaran;
use App\Models\WhatsAppReminderLog;
use Illuminate\Support\Carbon;

class SendWhatsAppReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $wargaId;
    public array $pembayaranIds;
    public int $tries = 3;
    public array $backoff = [60, 300];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $wargaId, array $pembayaranIds)
    {
        $this->wargaId = $wargaId;
        $this->pembayaranIds = $pembayaranIds;
        $this->onQueue('whatsapp');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $whatsAppService)
    {
        $items = Pembayaran::with('jenisPembayaran', 'warga')
            ->whereIn('id', $this->pembayaranIds)
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        $first = $items->first();
        $warga = $first->warga;
        $noHp = (string) ($warga?->no_hp ?? '');

        if (trim($noHp) === '') {
            WhatsAppReminderLog::create([
                'warga_id' => $this->wargaId,
                'recipient' => $noHp,
                'status' => 'failed',
                'message' => 'No phone number',
                'error_message' => 'No phone number for warga',
            ]);
            return;
        }

        $nama = (string) ($warga?->nama ?? 'Warga');
        $countTagihan = $items->count();
        $totalTagihan = (int) $items->sum('jumlah');
        $totalDenda = (int) $items->sum('denda');

        $detailLines = $items->take(3)->map(function (Pembayaran $item) {
            $periode = (string) ($item->periode ?? '-');
            $jatuhTempo = $item->jatuh_tempo
                ? Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y')
                : '-';

            return '• ' . (optional($item->jenisPembayaran)->nama ?: '-') . ' (' . $periode . ') — Rp ' . number_format((int) $item->jumlah, 0, ',', '.') . ' — Jt: ' . $jatuhTempo;
        })->values()->all();

        $paymentUrl = route('pembayaran.invoice', $first->id);

        $bodyLines = [];
        $bodyLines[] = '🔔 PENGINGAT TAGIHAN — Desa Anda';
        $bodyLines[] = 'Halo ' . $nama . ',';
        $bodyLines[] = 'Anda memiliki ' . $countTagihan . ' tagihan yang belum lunas.';
        $bodyLines[] = '';
        $bodyLines[] = '— Rincian tagihan —';
        foreach ($detailLines as $l) {
            $bodyLines[] = $l;
        }
        $bodyLines[] = '';
        $bodyLines[] = 'Total tunggakan: Rp ' . number_format($totalTagihan, 0, ',', '.');
        $bodyLines[] = 'Total denda: Rp ' . number_format($totalDenda, 0, ',', '.');
        $bodyLines[] = '';
        $bodyLines[] = '[ BAYAR SEKARANG ]: ' . $paymentUrl;
        $bodyLines[] = '';
        $bodyLines[] = "Jika Anda sudah membayar, abaikan pesan ini.";
        $bodyLines[] = "Balas 'BERHENTI' untuk berhenti menerima pengingat.";

        $message = implode("\n", $bodyLines);

        $sent = $whatsAppService->send($noHp, $message);
        $last = $whatsAppService->getLastResponse();

        if ($sent) {
            Pembayaran::whereIn('id', $this->pembayaranIds)->update(['last_whatsapp_reminder_at' => now()->toDateString()]);
            WhatsAppReminderLog::create([
                'pembayaran_id' => $first->id,
                'warga_id' => $this->wargaId,
                'recipient' => $noHp,
                'status' => 'sent',
                'message' => $message,
                'sent_at' => now(),
            ]);
        } else {
            WhatsAppReminderLog::create([
                'pembayaran_id' => $first->id,
                'warga_id' => $this->wargaId,
                'recipient' => $noHp,
                'status' => 'failed',
                'message' => $message,
                'error_message' => $last ? json_encode($last) : 'Provider failed',
            ]);
            // Biarkan gagal tanpa melemparkan Exception agar halaman website tidak error (crash)
        }
    }
}
