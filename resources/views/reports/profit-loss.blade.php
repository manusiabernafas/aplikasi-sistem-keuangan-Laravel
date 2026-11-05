<x-app-layout :title="$title">
    <x-page-header :title="$title" :description="'Periode: '.$periodLabel">
        <x-slot name="actions">
            <x-report-actions route="reports.profit-loss" :params="request()->only(['date_from','date_to'])" />
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
            <x-stat-card label="Total Pemasukan" value="Rp {{ number_format($data['income'], 0, ',', '.') }}" icon="bi-arrow-down-circle" variant="success" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Pengeluaran" value="Rp {{ number_format($data['expense'], 0, ',', '.') }}" icon="bi-arrow-up-circle" variant="danger" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Net Profit" value="Rp {{ number_format($data['net_profit'], 0, ',', '.') }}" icon="bi-graph-up-arrow" :variant="$data['net_profit'] >= 0 ? 'success' : 'danger'" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3 text-success">Pemasukan per Kategori</h2>
                    @forelse ($data['income_by_category'] as $row)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $row['category'] }}</span>
                            <span class="fw-medium">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3 text-danger">Pengeluaran per Kategori</h2>
                    @forelse ($data['expense_by_category'] as $row)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $row['category'] }}</span>
                            <span class="fw-medium">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-secondary small mb-0">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
