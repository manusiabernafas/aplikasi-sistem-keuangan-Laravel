<x-app-layout title="Rekonsiliasi">
    <x-page-header title="Rekonsiliasi" description="Cocokkan saldo sistem dengan mutasi rekening/bank statement.">
        <x-slot name="actions">
            @can('create', \App\Models\Reconciliation::class)
                <a href="{{ route('reconciliations.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Mulai Rekonsiliasi</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($reconciliations->isEmpty())
                <x-empty-state icon="bi-check2-square" title="Belum ada rekonsiliasi" description="Mulai rekonsiliasi untuk mencocokkan saldo sistem dengan mutasi bank." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Account</th>
                                <th>Tgl Statement</th>
                                <th class="text-end">Saldo Statement</th>
                                <th class="text-end">Saldo Sistem</th>
                                <th class="text-end">Selisih</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reconciliations as $reconciliation)
                                <tr>
                                    <td class="fw-medium">{{ $reconciliation->account->account_name }}</td>
                                    <td>{{ $reconciliation->statement_date->format('d/m/Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($reconciliation->statement_balance, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($reconciliation->system_balance, 0, ',', '.') }}</td>
                                    <td class="text-end {{ (float) $reconciliation->difference != 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($reconciliation->difference, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $reconciliation->status === 'COMPLETED' ? 'success' : 'warning' }}-subtle">{{ $reconciliation->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('reconciliations.show', $reconciliation) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $reconciliations->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
