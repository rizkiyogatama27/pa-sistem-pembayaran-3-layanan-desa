# Checklist Demo Aman (Payment Management)

## 1) Persiapan Sebelum Demo

- Jalankan `php artisan optimize:clear`.
- Pastikan migrasi status `Ran`: `php artisan migrate:status`.
- Pastikan data aman:
  - `null_jatuh_tempo = 0`
  - `duplikat_periode = 0`

## 2) Skenario Admin

1. Login sebagai admin.
2. Buka Payment Management.
3. Filter data berdasarkan periode, jenis, dan status.
4. Buka salah satu tagihan pending lalu bayar via tunai.
5. Cek status berubah menjadi paid.
6. Coba hapus tagihan paid (harus ditolak sistem).

## 3) Skenario User

1. Login sebagai user yang sudah terhubung ke warga.
2. Saat login, sistem otomatis memastikan tagihan periode aktif tersedia.
3. Buka menu Tagihan Saya.
4. Verifikasi tagihan ada dan kolom tenggat terisi.
5. Coba bayar salah satu tagihan via online/tunai sesuai alur yang tersedia.

## 4) Bukti Requirement Dosen

- Tagihan bulanan otomatis dibuat saat warga didaftarkan.
- Tagihan periode aktif juga dipastikan saat user login.
- Tenggat (`jatuh_tempo`) otomatis terisi.
- Data dijaga agar tidak duplikat per kombinasi: warga + jenis + periode.

## 5) Kalimat Singkat Saat Presentasi

"Di sistem ini, tagihan bulanan tidak diinput manual satu per satu. Saat warga terdaftar dan saat user login, sistem otomatis memastikan tagihan periode aktif sudah terbentuk lengkap dengan tenggat, lalu admin tinggal memonitor dan memproses pembayarannya."
