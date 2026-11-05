<x-app-layout title="Rekonsiliasi">
    <x-page-header :title="'Rekonsiliasi ' . $reconciliation->account->account_name" :description="$reconciliation->statement_date->format('d/m/Y')">
        <x-slot name="actions">
            @if ($reconciliation->status === 'IN_PROGRESS')
                <form method="POST" action="{{ route('reconciliations.complete', $reconciliation) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Selesaikan Rekonsiliasi</button>
                </form>
            @else
                <span class="badge text-bg-success-subtle text-success-emphasis">Selesai pada {{ $reconciliation->completed_at->format('d/m/Y H:i') }}</span>
            @endif
        </x-slot>
    </x-page-header>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Saldo Statement" value="Rp {{ number_format($reconciliation->statement_balance, 0, ',', '.') }}" icon="bi-bank" variant="primary" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Saldo Sistem" value="Rp {{ number_format($reconciliation->system_balance, 0, ',', '.') }}" icon="bi-laptop" variant="info" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Selisih" value="Rp {{ number_format($reconciliation->difference, 0, ',', '.') }}" icon="bi-arrow-left-right" :variant="(float) $reconciliation->difference == 0 ? 'success' : 'danger'" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Transaksi Matched" value="{{ $matchedCount }} / {{ $transactions->count() }}" icon="bi-check2-square" variant="success" />
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($transactions->isEmpty())
                <x-empty-state icon="bi-receipt" title="Tidak ada transaksi untuk dicocokkan" description="Semua transaksi pada account ini sudah direkonsiliasi atau belum ada transaksi." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Tipe</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Matched</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr class="{{ $transaction->is_matched_here ? 'table-success' : '' }}">
                                    <td class="text-nowrap">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                    <td><code class="small">{{ $transaction->transaction_number }}</code></td>
                                    <td><span class="badge {{ $transaction->badgeClass() }}">{{ $transaction->typeLabel() }}</span></td>
                                    <td>{{ $transaction->description ?: '—' }}</td>
                                    <td class="text-end">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($reconciliation->status === 'IN_PROGRESS')
                                            <form method="POST" action="{{ route('reconciliations.toggle', [$reconciliation, $transaction]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $transaction->is_matched_here ? 'btn-success' : 'btn-outline-secondary' }}">
                                                    <i class="bi {{ $transaction->is_matched_here ? 'bi-check-lg' : 'bi-square' }}"></i>
                                                </button>
                                            </form>
                                        @else
                                            <i class="bi {{ $transaction->is_matched_here ? 'bi-check-lg text-success' : 'bi-x-lg text-danger' }}"></i>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
