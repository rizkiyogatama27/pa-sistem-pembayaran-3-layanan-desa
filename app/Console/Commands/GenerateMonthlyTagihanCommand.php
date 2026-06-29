<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AutoGenerateTagihanService;
use App\Models\Warga;
use Illuminate\Console\Command;

class GenerateMonthlyTagihanCommand extends Command
{
    protected $signature = 'tagihan:generate-bulanan {--periode=}';

    protected $description = 'Generate tagihan bulanan otomatis untuk seluruh warga dan jenis pembayaran berulang';

    public function handle(AutoGenerateTagihanService $autoGenerateTagihanService): int
    {
        $periode = (string) ($this->option('periode') ?: now()->format('Y-m'));

        $wargas = Warga::query()->where('status', 'aktif')->orderBy('nama')->get();

        if ($wargas->isEmpty()) {
            $this->warn('Tidak ada data warga.');

            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($wargas as $warga) {
            $virtualUser = new User();
            $virtualUser->warga_id = $warga->id;
            $generated += $autoGenerateTagihanService->generateForUser($virtualUser, $periode);
        }

        $this->info("Generated {$generated} tagihan otomatis untuk periode {$periode}.");

        return self::SUCCESS;
    }
}
