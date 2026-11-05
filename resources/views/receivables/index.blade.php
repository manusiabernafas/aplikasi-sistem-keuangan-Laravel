<x-app-layout title="Piutang">
    <x-page-header title="Piutang" description="Kelola piutang dan penerimaan pembayarannya.">
        <x-slot name="actions">
            @can('create', \App\Models\Receivable::class)
                <a href="{{ route('receivables.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah Piutang</a>
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

            @if ($receivables->isEmpty())
                <x-empty-state icon="bi-cash-coin" title="Belum ada piutang tercatat" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>No. Piutang</th>
                                <th>Pihak</th>
                                <th>Jatuh Tempo</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Diterima</th>
                                <th class="text-end">Sisa</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receivables as $receivable)
                                <tr>
                                    <td><code class="small">{{ $receivable->receivable_number }}</code></td>
                                    <td class="fw-medium">{{ $receivable->debtor_name }}</td>
                                    <td class="text-nowrap">{{ $receivable->due_date->format('d/m/Y') }}</td>
                                    <td class="text-end">Rp {{ number_format($receivable->amount, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($receivable->totalReceived(), 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($receivable->remaining(), 0, ',', '.') }}</td>
                                    <td><span class="badge {{ $receivable->statusBadgeClass() }}">{{ $receivable->status }}</span></td>
                                    <td class="text-end">
                                        @can('update', $receivable)
                                            <a href="{{ route('receivables.edit', $receivable) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $receivable)
                                            <form action="{{ route('receivables.destroy', $receivable) }}" method="POST" class="d-inline" data-confirm-delete="Hapus piutang {{ $receivable->debtor_name }}?">
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
                <div class="mt-3">{{ $receivables->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
