<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionNumberSequence;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class TransactionNumberService
{
    /**
     * @var array<string, string>
     */
    private const PREFIXES = [
        Transaction::TYPE_INCOME => 'INC',
        Transaction::TYPE_EXPENSE => 'EXP',
        Transaction::TYPE_TRANSFER => 'TRF',
        Transaction::TYPE_ADJUSTMENT => 'ADJ',
    ];

    public function generate(string $transactionType, CarbonInterface $date): string
    {
        return $this->generateWithPrefix(self::PREFIXES[$transactionType] ?? 'TRX', $date);
    }

    /**
     * Generic atomic, per-day sequence generator shared by any module that
     * needs a human-readable unique number (transactions, debts, receivables, ...).
     */
    public function generateWithPrefix(string $prefix, CarbonInterface $date): string
    {
        $dateOnly = $date->toDateString();

        return retry(5, function () use ($prefix, $dateOnly, $date) {
            return DB::transaction(function () use ($prefix, $dateOnly, $date) {
                $sequence = TransactionNumberSequence::where('prefix', $prefix)
                    ->where('sequence_date', $dateOnly)
                    ->lockForUpdate()
                    ->first();

                if (! $sequence) {
                    $sequence = TransactionNumberSequence::create([
                        'prefix' => $prefix,
                        'sequence_date' => $dateOnly,
                        'last_number' => 0,
                    ]);
                }

                $nextNumber = $sequence->last_number + 1;
                $sequence->update(['last_number' => $nextNumber]);

                return sprintf('%s-%s-%06d', $prefix, $date->format('Ymd'), $nextNumber);
            });
        }, 50);
    }
}
