<x-app-layout title="Hutang">
    <x-page-header title="Hutang" description="Kelola hutang dan cicilan pembayarannya.">
        <x-slot name="actions">
            @can('create', \App\Models\Debt::class)
                <a href="{{ route('debts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Hutang</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="OUTSTANDING" @selected(request('status') === 'OUTSTANDING')>Outstanding</option>
                        <option value="PARTIALLY_PAID" @selected(request('status') === 'PARTIALLY_PAID')>Sebagian Lunas</option>
                        <option value="PAID" @selected(request('status') === 'PAID')>Lunas</option>
                        <option value="OVERDUE" @selected(request('status') === 'OVERDUE')>Overdue</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>

            @if ($debts->isEmpty())
                <x-empty-state icon="bi-credit-card" title="Belum ada hutang tercatat" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>No. Hutang</th>
                                <th>Kreditur</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($debts as $debt)
                                <tr>
                                    <td><code class="small">{{ $debt->debt_number }}</code></td>
                                    <td class="fw-medium">{{ $debt->creditor_name }}</td>
                                    <td class="text-nowrap">{{ $debt->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($debt->amount, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($debt->totalPaid(), 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($debt->remaining(), 0, ',', '.') }}</td>
                                    <td><span class="badge {{ $debt->statusBadgeClass() }}">{{ $debt->status }}</span></td>
                                    <td class="text-end">
                                        @can('update', $debt)
                                            <a href="{{ route('debts.edit', $debt) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $debt)
                                            <form action="{{ route('debts.destroy', $debt) }}" method="POST" class="d-inline" data-confirm-delete="Hapus hutang {{ $debt->creditor_name }}?">
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
                <div class="mt-3">{{ $debts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
