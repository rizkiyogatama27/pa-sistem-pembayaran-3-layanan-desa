<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Models\WhatsAppReminderLog;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendWhatsAppReminderCommand extends Command
{
    protected $signature = 'tagihan:send-whatsapp-reminder {--limit=25} {--days=5}';

    protected $description = 'Kirim reminder WhatsApp otomatis untuk tagihan yang jatuh tempo';

    public function handle(): int
    {
        $whatsAppService = app(WhatsAppService::class);

        if (! $whatsAppService->enabled()) {
            $this->warn('WhatsApp belum diaktifkan.');

            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $reminderDaysBeforeDue = max(0, (int) $this->option('days'));
        $today = Carbon::today();
        $targetDueDate = $today->copy()->addDays($reminderDaysBeforeDue);
        $reminderLabel = $reminderDaysBeforeDue === 0 ? 'hari ini' : $reminderDaysBeforeDue . ' hari lagi';

        $pembayarans = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->where('status', 'pending')
            ->where('jumlah', '>', 0)
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '=', $targetDueDate->toDateString())
            ->where(function ($query) use ($today) {
                $query->whereNull('last_whatsapp_reminder_at')
                    ->orWhereDate('last_whatsapp_reminder_at', '<', $today);
            })
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->whereHas('warga', function ($query) {
                $query->whereNotNull('no_hp');
            })
            ->orderBy('jatuh_tempo')
            ->limit($limit)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($pembayarans as $pembayaran) {
            $nama = (string) optional($pembayaran->warga)->nama;
            $noHp = (string) optional($pembayaran->warga)->no_hp;
            $periode = (string) ($pembayaran->periode ?? now()->format('Y-m'));
            $jatuhTempo = Carbon::parse($pembayaran->jatuh_tempo)->translatedFormat('d M Y');

            $message = implode("\n", [
                'Halo ' . $nama . ',',
                'Pengingat pembayaran: Tagihan Air periode ' . $periode . ' sebesar Rp ' . number_format($pembayaran->jumlah, 0, ',', '.') . ' akan jatuh tempo ' . $reminderLabel . '.',
                'Jatuh tempo: ' . $jatuhTempo,
                'Invoice: ' . ($pembayaran->invoice ?? '-'),
                'Silakan login ke portal untuk melakukan pembayaran.',
            ]);

            if ($whatsAppService->send($noHp, $message)) {
                $pembayaran->last_whatsapp_reminder_at = $today->toDateString();
                $pembayaran->save();

                WhatsAppReminderLog::create([
                    'pembayaran_id' => $pembayaran->id,
                    'warga_id' => $pembayaran->warga_id,
                    'recipient' => $noHp,
                    'status' => 'sent',
                    'message' => $message,
                    'sent_at' => now(),
                ]);

                $sent++;
            } else {
                WhatsAppReminderLog::create([
                    'pembayaran_id' => $pembayaran->id,
                    'warga_id' => $pembayaran->warga_id,
                    'recipient' => $noHp,
                    'status' => 'failed',
                    'message' => $message,
                    'error_message' => 'Provider mengembalikan gagal kirim.',
                ]);

                $failed++;
            }
        }

        $this->info("Reminder WhatsApp terkirim ke {$sent} tagihan, gagal {$failed} tagihan.");

        return self::SUCCESS;
    }
}