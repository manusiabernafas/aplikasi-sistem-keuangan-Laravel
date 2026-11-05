<x-app-layout :title="$title">
    <x-page-header :title="$title" :description="'Periode: '.$periodLabel">
        <x-slot name="actions">
            <x-report-actions route="reports.expense" :params="request()->only(['date_from','date_to','account_id','category_id','search'])" />
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm mb-3 no-print">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-6 col-md-2">
                    <x-input-label for="date_from" value="Dari" />
                    <x-text-input id="date_from" name="date_from" type="date" :value="request('date_from', $from->toDateString())" />
                </div>
                <div class="col-6 col-md-2">
                    <x-input-label for="date_to" value="Sampai" />
                    <x-text-input id="date_to" name="date_to" type="date" :value="request('date_to', $to->toDateString())" />
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label for="account_id" value="Account" />
                    <select id="account_id" name="account_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected((string) request('account_id') === (string) $account->id)>{{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <x-input-label for="category_id" value="Kategori" />
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h2 class="h6 fw-semibold mb-0">Detail Transaksi</h2>
                        <span class="fw-semibold text-danger">Total Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    @if ($transactions->isEmpty())
                        <x-empty-state icon="bi-arrow-up-circle" title="Tidak ada data pada periode ini" />
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-secondary small text-uppercase">
                                        <th>Tanggal</th><th>No.</th><th>Account</th><th>Kategori</th><th>Deskripsi</th><th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $t)
                                        <tr>
                                            <td class="text-nowrap">{{ $t->transaction_date->format('d/m/Y') }}</td>
                                            <td><code class="small">{{ $t->transaction_number }}</code></td>
                                            <td>{{ $t->account?->account_name }}</td>
                                            <td>{{ $t->category?->name }}</td>
                                            <td>{{ $t->description }}</td>
                                            <td class="text-end">Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Per Kategori</h2>
                    @forelse ($byCategory as $row)
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
