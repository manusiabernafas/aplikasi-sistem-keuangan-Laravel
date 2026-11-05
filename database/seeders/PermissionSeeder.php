<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function definitions(): array
    {
        return [
            'dashboard' => [
                'dashboard.view' => 'Lihat Dashboard',
            ],
            'transaction' => [
                'transaction.view' => 'Lihat Transaksi',
                'transaction.create' => 'Tambah Transaksi',
                'transaction.update' => 'Ubah Transaksi',
                'transaction.delete' => 'Hapus Transaksi',
            ],
            'account' => [
                'account.manage' => 'Kelola Account',
            ],
            'category' => [
                'category.manage' => 'Kelola Kategori',
            ],
            'budget' => [
                'budget.manage' => 'Kelola Budget',
            ],
            'debt' => [
                'debt.manage' => 'Kelola Hutang',
            ],
            'receivable' => [
                'receivable.manage' => 'Kelola Piutang',
            ],
            'reconciliation' => [
                'reconciliation.manage' => 'Kelola Rekonsiliasi',
                'reconciliation.override' => 'Ubah Transaksi Ter-rekonsiliasi',
            ],
            'report' => [
                'report.view' => 'Lihat Laporan',
                'report.export' => 'Export Laporan',
            ],
            'user' => [
                'user.manage' => 'Kelola User & Role',
            ],
            'setting' => [
                'setting.manage' => 'Kelola Pengaturan Sistem',
            ],
            'audit' => [
                'audit.view' => 'Lihat Audit Log',
            ],
            'backup' => [
                'backup.manage' => 'Kelola Backup Database',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::definitions() as $module => $permissions) {
            foreach ($permissions as $slug => $name) {
                Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'module' => $module]
                );
            }
        }
    }
}
