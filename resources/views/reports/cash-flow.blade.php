<x-app-layout :title="$title">
    <x-page-header :title="$title" :description="'Periode: '.$periodLabel">
        <x-slot name="actions">
            <x-report-actions route="reports.cash-flow" :params="request()->only(['date_from','date_to'])" />
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm mb-3 no-print">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-6 col-md-3">
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" name="date_from" type="date" :value="request('date_from', $from->toDateString())" />
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" name="date_to" type="date" :value="request('date_to', $to->toDateString())" />
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Pemasukan" value="Rp {{ number_format($data['total_income'], 0, ',', '.') }}" icon="bi-arrow-down-circle" variant="success" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Pengeluaran" value="Rp {{ number_format($data['total_expense'], 0, ',', '.') }}" icon="bi-arrow-up-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Net Cash Flow" value="Rp {{ number_format($data['total_net'], 0, ',', '.') }}" icon="bi-graph-up" :variant="$data['total_net'] >= 0 ? 'success' : 'danger'" />
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th>Periode</th>
                            <th class="text-end">Pemasukan</th>
                            <th class="text-end">Pengeluaran</th>
                            <th class="text-end">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['buckets'] as $bucket)
                            <tr>
                                <td>{{ $bucket['label'] }}</td>
                                <td class="text-end text-success">Rp {{ number_format($bucket['income'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($bucket['expense'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($bucket['net'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
