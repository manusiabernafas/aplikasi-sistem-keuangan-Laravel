<x-app-layout :title="$title">
    <x-page-header :title="$title" :description="'Ringkasan bulanan tahun '.$year">
        <x-slot name="actions">
            <x-report-actions route="reports.summary" :params="['year' => $year]" />
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm mb-3 no-print">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-6 col-md-3">
                    <x-input-label for="year" value="Tahun" />
                    <x-text-input id="year" name="year" type="number" :value="$year" min="2000" max="2100" />
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th>Bulan</th>
                            <th class="text-end">Pemasukan</th>
                            <th class="text-end">Pengeluaran</th>
                            <th class="text-end">Net</th>
                            <th class="text-end">Jml Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end text-success">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($row['expense'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($row['net'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ $row['transaction_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-semibold">
                            <td>Total</td>
                            <td class="text-end text-success">Rp {{ number_format(collect($rows)->sum('income'), 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format(collect($rows)->sum('expense'), 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format(collect($rows)->sum('net'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ collect($rows)->sum('transaction_count') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
