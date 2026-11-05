<x-app-layout title="Transaksi">
    <x-page-header title="Semua Transaksi" description="Daftar seluruh pemasukan, pengeluaran, dan transfer.">
        <x-slot name="actions">
            @can('create', \App\Models\Transaction::class)
                <a href="{{ route('transactions.income.create') }}" class="btn btn-success btn-sm"><i class="bi bi-arrow-down-circle me-1"></i>Pemasukan</a>
                <a href="{{ route('transactions.expense.create') }}" class="btn btn-danger btn-sm"><i class="bi bi-arrow-up-circle me-1"></i>Pengeluaran</a>
                <a href="{{ route('transactions.transfer.create') }}" class="btn btn-info btn-sm text-white"><i class="bi bi-arrow-left-right me-1"></i>Transfer</a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari no. transaksi / deskripsi...">
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        <option value="INCOME" @selected(request('type') === 'INCOME')>Pemasukan</option>
                        <option value="EXPENSE" @selected(request('type') === 'EXPENSE')>Pengeluaran</option>
                        <option value="TRANSFER" @selected(request('type') === 'TRANSFER')>Transfer</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="account_id" class="form-select form-select-sm">
                        <option value="">Semua Account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected((string) request('account_id') === (string) $account->id)>{{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->fullName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="POSTED" @selected(request('status') === 'POSTED')>Posted</option>
                        <option value="VOID" @selected(request('status') === 'VOID')>Void</option>
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-funnel"></i></button>
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <input type="number" name="amount_min" value="{{ request('amount_min') }}" class="form-control form-control-sm" placeholder="Nominal min">
                </div>
                <div class="col-6 col-md-3">
                    <input type="number" name="amount_max" value="{{ request('amount_max') }}" class="form-control form-control-sm" placeholder="Nominal max">
                </div>
            </form>

            @if ($transactions->isEmpty())
                <x-empty-state icon="bi-receipt" title="Belum ada transaksi" description="Mulai catat pemasukan atau pengeluaran pertama Anda.">
                    <x-slot name="action">
                        @can('create', \App\Models\Transaction::class)
                            <a href="{{ route('transactions.income.create') }}" class="btn btn-primary btn-sm">Tambah Transaksi</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Tanggal</th>
                                <th>No. Transaksi</th>
                                <th>Tipe</th>
                                <th>Account</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Nominal</th>
                                <th>Status</th>
                                <th>User</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td class="text-nowrap">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                    <td class="text-nowrap"><code class="small">{{ $transaction->transaction_number }}</code></td>
                                    <td><span class="badge {{ $transaction->badgeClass() }}">{{ $transaction->typeLabel() }}</span></td>
                                    <td>
                                        {{ $transaction->account?->account_name }}
                                        @if ($transaction->transaction_type === 'TRANSFER')
                                            <i class="bi bi-arrow-right mx-1 text-secondary"></i> {{ $transaction->destinationAccount?->account_name }}
                                        @endif
                                    </td>
                                    <td>{{ $transaction->category?->name ?? '—' }}</td>
                                    <td class="text-truncate" style="max-width: 200px;">{{ $transaction->description ?: '—' }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($transaction->status === 'POSTED')
                                            <span class="badge text-bg-success-subtle text-success-emphasis">Posted</span>
                                        @else
                                            <span class="badge text-bg-secondary-subtle text-secondary-emphasis">Void</span>
                                        @endif
                                        @if ($transaction->is_reconciled)
                                            <span class="badge text-bg-info-subtle text-info-emphasis"><i class="bi bi-check2-square"></i></span>
                                        @endif
                                    </td>
                                    <td class="text-secondary small">{{ $transaction->createdBy?->name }}</td>
                                    <td class="text-end text-nowrap">
                                        @can('update', $transaction)
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $transaction)
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline" data-confirm-delete="Hapus transaksi {{ $transaction->transaction_number }}?">
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
                <div class="mt-3">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
