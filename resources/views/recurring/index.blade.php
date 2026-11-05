<x-app-layout title="Recurring Transaction">
    <x-page-header title="Transaksi Berulang" description="Jadwal otomatis untuk gaji, tagihan, dan transaksi rutin lainnya.">
        <x-slot name="actions">
            @can('create', \App\Models\RecurringTransaction::class)
                <a href="{{ route('recurring-transactions.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Recurring</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($recurringTransactions->isEmpty())
                <x-empty-state icon="bi-arrow-repeat" title="Belum ada transaksi berulang" description="Jadwalkan gaji, tagihan listrik, atau internet agar tercatat otomatis.">
                    <x-slot name="action">
                        @can('create', \App\Models\RecurringTransaction::class)
                            <a href="{{ route('recurring-transactions.create') }}" class="btn btn-primary btn-sm">Tambah Recurring</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Frekuensi</th>
                                <th class="text-end">Nominal</th>
                                <th>Next Run</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recurringTransactions as $recurring)
                                <tr>
                                    <td class="fw-medium">{{ $recurring->name }}</td>
                                    <td><span class="badge text-bg-light border">{{ $recurring->transaction_type }}</span></td>
                                    <td>{{ $recurring->frequencyLabel() }}</td>
                                    <td class="text-end">Rp {{ number_format($recurring->amount, 0, ',', '.') }}</td>
                                    <td>{{ $recurring->next_run_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $recurring->status === 'ACTIVE' ? 'success' : 'secondary' }}-subtle">{{ $recurring->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        @can('update', $recurring)
                                            <a href="{{ route('recurring-transactions.edit', $recurring) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $recurring)
                                            <form action="{{ route('recurring-transactions.destroy', $recurring) }}" method="POST" class="d-inline" data-confirm-delete="Hapus recurring {{ $recurring->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $recurringTransactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
