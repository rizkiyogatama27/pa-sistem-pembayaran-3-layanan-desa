<?php

namespace Database\Seeders;

use App\Models\Keluarga;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $keluarga1 = Keluarga::updateOrCreate(
            ['no_kk' => '3201010101010101'],
            [
                'nama_keluarga' => 'Keluarga Ahmad',
                'alamat' => 'Dusun Pusat, Desa Pangean',
            ]
        );

        $keluarga2 = Keluarga::updateOrCreate(
            ['no_kk' => '3201010101010102'],
            [
                'nama_keluarga' => 'Keluarga Siti',
                'alamat' => 'Dusun Selatan, Desa Pangean',
            ]
        );

        $keluarga3 = Keluarga::updateOrCreate(
            ['no_kk' => '3201010101010103'],
            [
                'nama_keluarga' => 'Keluarga Abdullah',
                'alamat' => 'Dusun Barat, Desa Pangean',
            ]
        );

        Warga::updateOrCreate(
            ['nik' => '3201010101010101'],
            [
                'keluarga_id' => $keluarga1->id,
                'nama' => 'Test User',
                'alamat' => 'Dusun Pusat, Desa Pangean',
                'no_hp' => '081234567890',
            ]
        );

        Warga::updateOrCreate(
            ['nik' => '3201010101010102'],
            [
                'keluarga_id' => $keluarga2->id,
                'nama' => 'Muhammad Rizki Yogatama',
                'alamat' => 'Dusun Selatan, Desa Pangean',
                'no_hp' => '081234567891',
            ]
        );

        // Tambah warga untuk David dan keluarganya
        Warga::updateOrCreate(
            ['nik' => '3201010101010103'],
            [
                'keluarga_id' => $keluarga3->id,
                'nama' => 'David Abdullah',
                'alamat' => 'Dusun Barat, Desa Pangean',
                'no_hp' => '081234567892',
            ]
        );

        Warga::updateOrCreate(
            ['nik' => '3201010101010104'],
            [
                'keluarga_id' => $keluarga3->id,
                'nama' => 'Andi Abdullah',
                'alamat' => 'Dusun Barat, Desa Pangean',
                'no_hp' => '081234567893',
            ]
        );

        Warga::updateOrCreate(
            ['nik' => '3201010101010105'],
            [
                'keluarga_id' => $keluarga3->id,
                'nama' => 'Ibu Sari Abdullah',
                'alamat' => 'Dusun Barat, Desa Pangean',
                'no_hp' => '081234567894',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'verification_status' => 'approved',
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'nik' => '3201010101010101',
                'kk' => '3201010101010101',
                'verification_status' => 'approved',
                'warga_id' => Warga::where('nik', '3201010101010101')->value('id'),
                'keluarga_id' => $keluarga1->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'rizkiyoga2005@gmail.com'],
            [
                'name' => 'Muhammad Rizki Yogatama',
                'password' => Hash::make('password'),
                'role' => 'user',
                'nik' => '3201010101010102',
                'kk' => '3201010101010102',
                'verification_status' => 'approved',
                'warga_id' => Warga::where('nik', '3201010101010102')->value('id'),
                'keluarga_id' => $keluarga2->id,
            ]
        );
    }
}
