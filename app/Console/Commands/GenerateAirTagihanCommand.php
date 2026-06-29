<?php

namespace App\Console\Commands;

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\Warga;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateAirTagihanCommand extends Command
{
    protected $signature = 'tagihan:generate-air {--periode=}';

    protected $description = 'Generate draft tagihan air bulanan untuk setiap warga';

    public function handle(): int
    {
        $periode = (string) ($this->option('periode') ?: now()->format('Y-m'));
        $tanggalTagihan = Carbon::now()->toDateString();
        $jatuhTempo = Carbon::now()->addDays(10)->toDateString();

        $jenisAir = JenisPembayaran::query()
            ->whereRaw('LOWER(nama) like ?', ['%air%'])
            ->first();

        if (! $jenisAir) {
            $this->error('Jenis pembayaran air belum tersedia.');

            return self::FAILURE;
        }

        $wargas = Warga::query()->where('status', 'aktif')->orderBy('nama')->get();
        $generated = 0;

        foreach ($wargas as $warga) {
            $exists = Pembayaran::query()
                ->where('warga_id', $warga->id)
                ->where('jenis_pembayaran_id', $jenisAir->id)
                ->where('periode', $periode)
                ->exists();

            if ($exists) {
                continue;
            }

            $previousAir = Pembayaran::query()
                ->where('warga_id', $warga->id)
                ->where('jenis_pembayaran_id', $jenisAir->id)
                ->whereNotNull('meter_akhir')
                ->orderByDesc('tanggal_bayar')
                ->first();

            $meterAwal = (int) ($previousAir->meter_akhir ?? 0);

            Pembayaran::create([
                'warga_id' => $warga->id,
                'jenis_pembayaran_id' => $jenisAir->id,
                'tanggal_bayar' => $tanggalTagihan,
                'periode' => $periode,
                'meter_awal' => $meterAwal,
                'meter_akhir' => $meterAwal,
                'pemakaian_air' => 0,
                'tarif_per_meter' => 1500,
                'biaya_tetap' => 5000,
                'denda' => 0,
                'jatuh_tempo' => $jatuhTempo,
                'jumlah' => 0,
                'keterangan' => 'Tagihan air otomatis periode ' . $periode . '. Silakan isi meter akhir sebelum pembayaran.',
                'status' => 'pending',
                'invoice' => 'INV-GEN-' . str_replace('-', '', $periode) . '-' . $warga->id . '-' . random_int(1000, 9999),
            ]);

            $generated++;
        }

        $this->info("Generated {$generated} draft tagihan air untuk periode {$periode}.");

        return self::SUCCESS;
    }
}