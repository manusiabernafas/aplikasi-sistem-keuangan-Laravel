# Keuang — Sistem Pencatatan Keuangan

Aplikasi manajemen keuangan pribadi/bisnis skala kecil-menengah: double-entry ledger, budget, hutang/piutang, rekonsiliasi, laporan, dan lainnya. Dibangun dengan Laravel 13, MySQL 8, dan Bootstrap 5.

## Fitur Utama

- **Transaksi**: Pemasukan, Pengeluaran, Transfer dengan ledger double-entry di balik layar dan strict balance validation.
- **Master Data**: Account (Kas/Bank/E-Wallet), Kategori (parent-child), Tag.
- **Perencanaan**: Budget dengan progress tracking, Recurring Transaction (terjadwal via scheduler), Financial Goals.
- **Hutang & Piutang**: Pencatatan dengan cicilan/partial payment, otomatis membuat entri ledger.
- **Rekonsiliasi**: Cocokkan saldo sistem dengan mutasi bank.
- **Laporan**: Cash Flow, Income, Expense, Profit & Loss, Net Worth, Financial Summary — export Excel/CSV/PDF + print.
- **Financial Insights**: analisis otomatis berbasis data (tanpa AI/API eksternal).
- **Notifikasi**: alert budget, hutang/piutang jatuh tempo, transaksi berulang.
- **Audit Log**, **Database Backup**, **RBAC** (Super Admin/Admin/Staff/Viewer) dengan permission granular.

## Requirement

- PHP 8.4+, ekstensi: `pdo_mysql`, `mbstring`, `gd`, `zip`, `xml`, `curl`
- MySQL 8+
- Node.js 20+ & npm
- `mysqldump` di PATH (untuk fitur Database Backup)

## Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD (buat database & user MySQL terlebih dahulu)

php artisan migrate --seed
php artisan storage:link
npm run build

php artisan serve
```

Kredensial Super Admin dicetak sekali di terminal saat `--seed` dijalankan (email `superadmin@keuang.local`). Segera login dan ganti password melalui halaman Profil.

## Development

```bash
npm run dev          # Vite dev server dengan hot reload
php artisan test     # Jalankan seluruh test suite (Feature + Unit)
php artisan migrate:fresh --seed   # Reset database dari kosong
```

## Scheduler (untuk Recurring Transaction & Notifikasi)

Daftarkan cron berikut agar `keuang:process-recurring-transactions` (01:00) dan `keuang:check-alerts` (06:00) berjalan otomatis:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Struktur Proyek

- `app/Services/` — business logic (LedgerService, TransactionService, BudgetService, dll). Controller tetap tipis.
- `app/Policies/` — otorisasi per-model, dievaluasi di backend (bukan hanya UI).
- `app/Console/Commands/` — `keuang:process-recurring-transactions`, `keuang:check-alerts`.
- `database/seeders/` — role/permission/akun/kategori default + sample data.

## Production Checklist

Sebelum deploy ke production, pastikan:
- `.env`: `APP_ENV=production`, `APP_DEBUG=false`
- `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- Scheduler terdaftar via cron (lihat di atas)
- Backup database dijadwalkan (via menu Backup atau cron eksternal)
# Keuangan
