# Sistem Informasi Verifikasi Dokumen SPRI (Surat Perjalanan Republik Indonesia)

Aplikasi berbasis web untuk tugas laporan Praktik Kerja Lapangan (PKL) yang mengelola verifikasi berkas permohonan paspor/SPRI pada kantor imigrasi serta menyediakan portal pengecekan status permohonan secara mandiri untuk pemohon.

Dibangun dengan arsitektur **Laravel 13 + PHP 8.5 + Blade + Tailwind CSS**, dirancang secara khusus dengan gaya visual *meja layanan resmi editorial* (bersih, fungsional, dan bebas template generik/anti-AI slop).

---

## 1. Fitur Utama

### A. Meja Layanan Admin / Verifikator (`/admin/verifications`)
1. **Autentikasi & Autorisasi Gate**: Hanya petugas dengan status admin (`is_admin = true`) yang dapat mengakses dashboard loket internal.
2. **Pengelolaan Berkas (CRUD)**:
   - Pencatatan berkas permohonan baru dengan nomor registrasi unik (misal: `SPRI-2026-X7K9P2`).
   - Filter berdasarkan status verifikasi dan pencarian multi-kolom (nomor registrasi atau nama pemohon).
   - Pembaruan berkas, status, serta catatan publik dan internal.
   - Penghapusan data menggunakan **Soft Deletes** (`deleted_at`), mencegah kehilangan riwayat permohonan secara tidak disengaja.
3. **Pemisahan Catatan Publik vs Internal**:
   - `public_notes`: Keterangan resmi yang dapat dibaca oleh pemohon pada portal pencarian publik (misal: arahan dokumen perbaikan).
   - `admin_notes`: Catatan rahasia loket (hasil telaah identitas, kesesuaian database, catatan wawancara) yang **tidak pernah dikirimkan atau ditampilkan ke publik**.
4. **Audit Trail Mutasi Otomatis**:
   - Setiap aksi `created`, `updated` (termasuk pergantian status `status_changed`), `deleted`, dan `restored` secara otomatis dicatat ke tabel `audit_logs` melalui Eloquent Observer.
   - Menyimpan informasi aktor (`user_id`), alamat IP (`ip_address`), `user_agent`, serta rekaman *before & after* perubahan.
5. **Kronologi Riwayat Status (Status Timeline)**:
   - Skema tabel `verification_status_histories` mencatat kronologi transisi status permohonan (`from_status` -> `to_status`), catatan pendukung, aktor pengubah (`changed_by`), dan waktu perubahan (`changed_at`).
   - Pencegahan duplikasi riwayat secara otomatis jika pembaruan berkas tidak mengubah nilai status verifikasi.
   - Ditampilkan dalam bentuk visual timeline terstruktur pada halaman detail berkas admin (`/admin/verifications/{id}`) dan terlindungi agar tidak pernah bocor ke portal publik.

### B. Portal Publik Cek Status Pemohon (`/cek-status`)
1. **Single-Page Mandiri**: Antarmuka responsif mobile-first berfokus pada kemudahan akses pemohon.
2. **Penyamaran Data Identitas (Data Masking)**: Nama lengkap pemohon disamarkan secara otomatis (contoh: `Muhammad Rizky` menjadi `M****** R****`) untuk melindungi privasi sesuai prinsip perlindungan data pribadi.
3. **Penyaringan Data Sensitif**: Tidak menampilkan ID internal database, NIK, alamat, file lampiran, maupun catatan internal admin loket.
4. **Keamanan & Rate Limiting**:
   - Dibatasi maksimal 10 permintaan pencarian per menit per alamat IP (`throttle:status-check`) untuk mencegah teknik *enumeration* atau *scraping*.
   - Normalisasi kode pencarian (otomatis trim & uppercase).
   - Penanganan respons generik saat nomor registrasi tidak ditemukan.

---

## 2. Struktur Status Verifikasi (`VerificationStatus`)

| Status | Kode DB | Label UI | Deskripsi Layanan |
|---|---|---|---|
| Menunggu | `pending` | Menunggu | Berkas permohonan telah tercatat dan sedang dalam antrean loket verifikator. |
| Sedang Diverifikasi | `in_review` | Sedang Diverifikasi | Petugas verifikator sedang memeriksa keabsahan berkas fisik dan data kependudukan. |
| Perlu Perbaikan | `needs_revision` | Perlu Perbaikan | Terdapat dokumen persyaratan yang kurang lengkap atau buram (wajib melampirkan catatan publik). |
| Disetujui | `approved` | Disetujui | Dokumen telah diverifikasi lengkap dan sah; tanggal verifikasi (`verified_at`) otomatis tercatat. |
| Ditolak | `rejected` | Ditolak | Permohonan tidak dapat disetujui berdasarkan regulasi Ditjen Imigrasi (wajib melampirkan alasan penolakan). |

---

## 3. Rute & Endpoint Aplikasi

| Metode | URL | Middleware / Guard | Keterangan |
|---|---|---|---|
| `GET` | `/` | — | Redirect ke `/cek-status` |
| `GET` | `/cek-status` | — | Form pencarian status publik |
| `POST` | `/cek-status` | `throttle:status-check` (10/min) | Proses pencarian status publik (aman & masked) |
| `GET` | `/login` | `guest` | Form login meja layanan petugas |
| `POST` | `/login` | `guest` | Autentikasi akun petugas |
| `POST` | `/logout` | `auth` | Keluar dari sesi administrasi |
| `GET` | `/admin/verifications` | `auth`, `can:admin` | Daftar seluruh berkas & statistik antrean |
| `GET` | `/admin/verifications/create` | `auth`, `can:admin` | Form pendaftaran berkas baru |
| `POST` | `/admin/verifications` | `auth`, `can:admin` | Simpan berkas baru ke database |
| `GET` | `/admin/verifications/{id}` | `auth`, `can:admin` | Detail dossier berkas & audit log mutasi |
| `GET` | `/admin/verifications/{id}/edit` | `auth`, `can:admin` | Form pembaruan berkas & status |
| `PUT` | `/admin/verifications/{id}` | `auth`, `can:admin` | Simpan perubahan berkas |
| `DELETE` | `/admin/verifications/{id}` | `auth`, `can:admin` | Soft delete berkas permohonan |

---

## 4. Panduan Instalasi & Menjalankan Aplikasi

### Persyaratan Sistem
- PHP >= 8.2 (dilengkapi ekstensi `pdo_sqlite`, `mbstring`, `xml`, `curl`)
- Composer >= 2.x
- Node.js & NPM (untuk build Tailwind CSS)

### Langkah Pemasangan
```bash
# 1. Masuk ke direktori proyek
cd /home/iki/projects/spri-app

# 2. Pasang dependensi PHP
composer install

# 3. Konfigurasi file environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi dan seeding data awal
php artisan migrate:fresh --seed

# 5. Pasang dependensi frontend dan build asset
npm install
npm run build

# 6. Jalankan server lokal
php artisan serve
```

Aplikasi dapat dibuka melalui browser pada alamat:
- **Portal Publik**: `http://127.0.0.1:8000/cek-status`
- **Login Admin**: `http://127.0.0.1:8000/login`

---

## 5. Akun Bawaan Seeder (Simulasi Lingkungan Dev)

Data awal telah disiapkan melalui `AdminUserSeeder` dan `VerificationSeeder`:

### Akun Petugas Loket (Admin):
- **Email**: `admin@imigrasi.go.id`
- **Kata Sandi**: `Password123!`
- **Peran**: Petugas Verifikator SPRI (`is_admin = true`)

### Contoh Nomor Registrasi untuk Uji Coba Publik:
1. `SPRI-2026-X7K9P2` — Status: **Disetujui** (Pemohon: `M****** R****`)
2. `SPRI-2026-M3W1Q8` — Status: **Sedang Diverifikasi** (Pemohon: `S*** N********`)
3. `SPRI-2026-B5N4T9` — Status: **Perlu Perbaikan** (Pemohon: `B*** S******`)
4. `SPRI-2026-R8L2J5` — Status: **Menunggu** (Pemohon: `D*** S******`)
5. `SPRI-2026-K1D6V4` — Status: **Ditolak** (Pemohon: `J*** S*****`)

---

## 6. Pengujian Otomatis (Automated Tests)

Seluruh logika bisnis, validasi, otorisasi, proteksi privasi, dan audit trail telah diuji secara menyeluruh melalui PHPUnit:

```bash
php artisan test
```

### Hasil Test Suite:
- `Tests\Unit\Enums\VerificationStatusTest`: Verifikasi enum, label Indonesia, dan css classes.
- `Tests\Feature\Auth\AdminAccessTest`: Pengujian proteksi guest, non-admin (403), login admin, dan logout.
- `Tests\Feature\Admin\VerificationCrudTest`: CRUD lengkap, validasi kode unik, filter, dan soft delete.
- `Tests\Feature\PublicStatusLookupTest`: Cek status publik, data masking, isolasi `admin_notes`, dan pencegahan XSS.
- `Tests\Feature\RateLimitTest`: Pembatasan 10 percobaan per menit (HTTP 429 pada percobaan ke-11).
- `Tests\Feature\AuditLogTest`: Verifikasi pencatatan otomatis audit mutasi (created, updated/status_changed, deleted).
- `Tests\Feature\VerificationStatusHistoryTest`: Verifikasi pencatatan status awal, transisi status, pencegahan duplikasi riwayat, tampilan timeline admin, dan proteksi isolasi data terhadap publik.

---

## 7. Catatan Batasan MVP (Laporan PKL)
Sesuai batasan rencana kerja (scope terkunci):
- Fokus pada sistem informasi pencatatan verifikasi berkas fisik dan pelacakan status.
- Belum mencakup modul biometrik fisik, sistem antrean tiket loket, pembayaran SIMKIM, maupun integrasi API langsung ke pusat Ditjen Imigrasi.
