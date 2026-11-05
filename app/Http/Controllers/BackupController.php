<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        private readonly BackupService $backupService,
        private readonly AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $backups = $this->backupService->list();

        return view('backups.index', compact('backups'));
    }

    public function store(): RedirectResponse
    {
        try {
            $filename = $this->backupService->create();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $this->auditLog->log('created', 'backup', null, null, ['filename' => $filename]);

        return back()->with('success', "Backup {$filename} berhasil dibuat.");
    }

    public function download(string $filename): StreamedResponse|RedirectResponse
    {
        try {
            return $this->backupService->download($filename);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroy(string $filename): RedirectResponse
    {
        $this->backupService->delete($filename);

        $this->auditLog->log('deleted', 'backup', null, ['filename' => $filename], null);

        return back()->with('success', 'Backup berhasil dihapus.');
    }
}
