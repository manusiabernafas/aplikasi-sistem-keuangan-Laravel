<x-app-layout :title="$title">
    <x-page-header :title="$title" description="Total aset dikurangi total liabilitas per hari ini.">
        <x-slot name="actions">
            <x-report-actions route="reports.net-worth" :params="[]" />
        </x-slot>
    </x-page-header>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Aset" value="Rp {{ number_format($data['total_assets'], 0, ',', '.') }}" icon="bi-piggy-bank" variant="success" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Total Liabilitas" value="Rp {{ number_format($data['total_liabilities'], 0, ',', '.') }}" icon="bi-credit-card" variant="danger" />
        </div>
        <div class="col-6 col-md-4">
            <x-stat-card label="Net Worth" value="Rp {{ number_format($data['net_worth'], 0, ',', '.') }}" icon="bi-gem" variant="primary" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Aset</h2>
                    @foreach ($data['assets'] as $label => $value)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $label }}</span>
                            <span class="fw-medium text-success">Rp {{ number_format($value, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Liabilitas</h2>
                    @foreach ($data['liabilities'] as $label => $value)
                        <div class="d-flex justify-content-between small mb-2">
                            <span>{{ $label }}</span>
                            <span class="fw-medium text-danger">Rp {{ number_format($value, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
