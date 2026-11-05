<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Thin abstraction around the backup mechanism so the underlying driver
 * (currently mysqldump) can be swapped without touching the controller/UI.
 */
class BackupService
{
    private const DISK = 'local';

    private const DIRECTORY = 'backups';

    /**
     * @return array<int, array{name: string, size: int, created_at: string}>
     */
    public function list(): array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }

        return collect($disk->files(self::DIRECTORY))
            ->filter(fn ($path) => str_ends_with($path, '.sql'))
            ->map(fn ($path) => [
                'name' => basename($path),
                'size' => $disk->size($path),
                'created_at' => date('Y-m-d H:i:s', $disk->lastModified($path)),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function create(): string
    {
        $config = config('database.connections.'.config('database.default'));
        $filename = 'backup_'.now()->format('Ymd_His').'.sql';
        $path = Storage::disk(self::DISK)->path(self::DIRECTORY.'/'.$filename);

        Storage::disk(self::DISK)->makeDirectory(self::DIRECTORY);

        $result = Process::env(['MYSQL_PWD' => $config['password']])
            ->run([
                'mysqldump',
                '-h', $config['host'],
                '-P', (string) $config['port'],
                '-u', $config['username'],
                '--single-transaction',
                '--skip-lock-tables',
                $config['database'],
                '--result-file='.$path,
            ]);

        if (! $result->successful()) {
            throw new RuntimeException('Backup gagal: '.$result->errorOutput());
        }

        return $filename;
    }

    public function download(string $filename): StreamedResponse
    {
        $path = self::DIRECTORY.'/'.$this->sanitize($filename);

        if (! Storage::disk(self::DISK)->exists($path)) {
            throw new RuntimeException('File backup tidak ditemukan.');
        }

        return Storage::disk(self::DISK)->download($path);
    }

    public function delete(string $filename): void
    {
        $path = self::DIRECTORY.'/'.$this->sanitize($filename);

        Storage::disk(self::DISK)->delete($path);
    }

    private function sanitize(string $filename): string
    {
        $filename = basename($filename);

        if (! preg_match('/^backup_\d{8}_\d{6}\.sql$/', $filename)) {
            throw new RuntimeException('Nama file backup tidak valid.');
        }

        return $filename;
    }
}
