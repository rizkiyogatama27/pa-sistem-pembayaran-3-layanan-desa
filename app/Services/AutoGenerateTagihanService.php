<?php

namespace App\Services;

use App\Models\JenisPembayaran;
use App\Models\Keluarga;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AutoGenerateTagihanService
{
    public function generateForUser(User $user, ?string $periode = null): int
    {
        $periode = $periode ?: now()->format('Y-m');
        $tanggalTagihan = Carbon::now()->toDateString();
        $jatuhTempo = Carbon::now()->addDays(10)->toDateString();

        $wargas = $this->resolveTargetWargas($user);

        if ($wargas->isEmpty()) {
            return 0;
        }

        // Fast exit: if any pembayaran already exists for this user+periode, skip the heavy generation loop.
        // This prevents slow logins for users who already have tagihan this month.
        $wargaIds = $wargas->pluck('id')->all();
        $alreadyExists = Pembayaran::whereIn('warga_id', $wargaIds)
            ->where('periode', $periode)
            ->exists();

        if ($alreadyExists) {
            return 0;
        }

        $jenisPembayarans = JenisPembayaran::query()
            ->where(function ($query) {
                $query->where('nominal', '>', 0)
                    ->orWhereRaw('LOWER(nama) like ?', ['%air%'])
                    ->orWhereRaw('LOWER(nama) like ?', ['%sampah%']);
            })
            ->whereRaw('LOWER(nama) not like ?', ['%donasi%'])
            ->orderBy('nama')
            ->get();

        if ($jenisPembayarans->isEmpty()) {
            return 0;
        }

        $generated = 0;

        foreach ($wargas as $warga) {
            foreach ($jenisPembayarans as $jenisPembayaran) {
                $existingPembayaran = Pembayaran::query()
                    ->where('warga_id', $warga->id)
                    ->where('jenis_pembayaran_id', $jenisPembayaran->id)
                    ->where('periode', $periode)
                    ->first();

                if ($existingPembayaran) {
                    if (empty($existingPembayaran->jatuh_tempo)) {
                        $baseDate = $existingPembayaran->tanggal_bayar
                            ? Carbon::parse((string) $existingPembayaran->tanggal_bayar)
                            : Carbon::now();

                        $existingPembayaran->jatuh_tempo = $baseDate->addDays(10)->toDateString();
                        $existingPembayaran->save();
                    }

                    continue;
                }

                $lowerNama = strtolower((string) $jenisPembayaran->nama);
                $isAir = str_contains($lowerNama, 'air');
                $isSampah = str_contains($lowerNama, 'sampah');

                $payload = [
                    'warga_id' => $warga->id,
                    'jenis_pembayaran_id' => $jenisPembayaran->id,
                    'tanggal_bayar' => $tanggalTagihan,
                    'periode' => $periode,
                    'status' => 'pending',
                    'jatuh_tempo' => $jatuhTempo,
                    'invoice' => 'INV-AUTO-' . now()->format('YmdHis') . '-' . $warga->id . '-' . $jenisPembayaran->id . '-' . random_int(100, 999),
                ];

                if ($isAir) {
                    $previousAir = Pembayaran::query()
                        ->where('warga_id', $warga->id)
                        ->where('jenis_pembayaran_id', $jenisPembayaran->id)
                        ->whereNotNull('meter_akhir')
                        ->orderByDesc('id')
                        ->first();

                    $meterAwal = (int) ($previousAir->meter_akhir ?? 0);

                    // Create draft tagihan for air with meter_akhir null so petugas dapat input later
                    $payload = array_merge($payload, [
                        'meter_awal' => $meterAwal,
                        'meter_akhir' => null,
                        'pemakaian_air' => 0,
                        'tarif_per_meter' => 1500,
                        'biaya_tetap' => 5000,
                        'denda' => 0,
                        'jumlah' => 0,
                        'keterangan' => 'Draft tagihan air periode ' . $periode . '. Silakan petugas input meter akhir.',
                    ]);
                } else {
                    if ($isSampah) {
                        $payload = array_merge($payload, [
                            'jumlah' => 10000,
                            'keterangan' => 'Tagihan Sampah (flat) periode ' . $periode,
                        ]);
                    } else {
                        $payload = array_merge($payload, [
                            'jumlah' => (int) $jenisPembayaran->nominal,
                            'keterangan' => 'Tagihan otomatis periode ' . $periode,
                        ]);
                    }
                }

                Pembayaran::create($payload);
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * Calculate water bill from meter readings.
     * Returns ['usage' => int, 'amount' => int]
     */
    public function calculateWaterBill(int $meterAwal, int $meterAkhir, int $ratePerMeter = 1500, int $fixed = 5000, int $minimum = 0): array
    {
        $usage = max(0, $meterAkhir - $meterAwal);
        $amount = $fixed + ($ratePerMeter * $usage);
        if ($minimum > 0 && $amount < $minimum) {
            $amount = $minimum;
        }

        return [
            'usage' => $usage,
            'amount' => (int) $amount,
        ];
    }

    private function resolveTargetWargas(User $user): Collection
    {
        // 1) direct warga_id
        if (! empty($user->warga_id)) {
            return Warga::query()->whereKey($user->warga_id)->get();
        }

        // 2) try match by NIK (if user provided NIK during registration)
        if (! empty($user->nik)) {
            $byNik = Warga::query()->where('nik', $user->nik)->get();
            if ($byNik->isNotEmpty()) {
                return $byNik;
            }
        }

        // 3) try match by KK (find keluarga first)
        if (! empty($user->kk)) {
            $kel = Keluarga::query()->where('no_kk', $user->kk)->first();
            if ($kel) {
                return Warga::query()->where('keluarga_id', $kel->id)->orderBy('nama')->get();
            }
        }

        // 4) fallback to keluarga_id if present
        if (! empty($user->keluarga_id)) {
            return Warga::query()->where('keluarga_id', $user->keluarga_id)->orderBy('nama')->get();
        }

        // 5) try match by name
        if (! empty($user->name)) {
            return Warga::query()->where('nama', $user->name)->limit(1)->get();
        }

        return new Collection();
    }
}
