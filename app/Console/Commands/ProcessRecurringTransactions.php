<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'keuang:process-recurring-transactions';

    protected $description = 'Generate transactions for due recurring schedules (idempotent per day).';

    public function handle(RecurringTransactionService $service): int
    {
        $systemUser = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->first();

        if (! $systemUser) {
            $this->error('Tidak ada Super Admin untuk menjalankan recurring transaction.');

            return self::FAILURE;
        }

        $result = $service->processDue($systemUser);

        $this->info("Recurring transactions processed: {$result['processed']} success, {$result['failed']} failed.");

        return self::SUCCESS;
    }
}
