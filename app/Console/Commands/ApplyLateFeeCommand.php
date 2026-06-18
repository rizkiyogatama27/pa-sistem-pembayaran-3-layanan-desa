<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ApplyLateFeeCommand extends Command
{
    protected $signature = 'tagihan:apply-denda';

    protected $description = 'Tambahkan denda otomatis untuk tagihan air yang melewati jatuh tempo';

    public function handle(): int
    {
        $today = Carbon::today();
        $lateFee = 2500;

        $pembayarans = Pembayaran::with('jenisPembayaran')
            ->where('status', 'pending')
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<', $today)
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->get();

        $updated = 0;

        foreach ($pembayarans as $pembayaran) {
            if ((int) $pembayaran->denda > 0) {
                continue;
            }

            $pembayaran->denda = $lateFee;
            $pembayaran->jumlah = (int) $pembayaran->jumlah + $lateFee;
            $note = 'Denda keterlambatan otomatis Rp ' . number_format($lateFee, 0, ',', '.') . ' pada ' . $today->toDateString();

            $pembayaran->keterangan = trim((string) $pembayaran->keterangan)
                ? trim($pembayaran->keterangan) . PHP_EOL . $note
                : $note;

            $pembayaran->save();
            $updated++;
        }

        $this->info("Denda otomatis diterapkan pada {$updated} tagihan.");

        return self::SUCCESS;
    }
}