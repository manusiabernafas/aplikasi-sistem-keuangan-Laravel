<x-app-layout title="Account">
    <x-page-header title="Account" description="Kelola kas, rekening bank, dan e-wallet.">
        <x-slot name="actions">
            @can('create', \App\Models\Account::class)
                <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Account
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <x-stat-card label="Total Saldo Aktif" value="Rp {{ number_format($totalBalance, 0, ',', '.') }}" icon="bi-wallet2" variant="primary" />
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama atau kode account...">
                </div>
                <div class="col-6 col-md-3">
                    <select name="account_type_id" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        @foreach ($accountTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('account_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>Aktif</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>

            @if ($accounts->isEmpty())
                <x-empty-state icon="bi-bank" title="Belum ada account" description="Tambahkan account pertama seperti Kas atau Rekening Bank.">
                    <x-slot name="action">
                        @can('create', \App\Models\Account::class)
                            <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-sm">Tambah Account</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th class="text-end">Saldo</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accounts as $account)
                                <tr>
                                    <td class="text-secondary">{{ $account->account_code }}</td>
                                    <td class="fw-medium">{{ $account->account_name }}</td>
                                    <td><span class="badge text-bg-light border">{{ $account->accountType->name }}</span></td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($account->isActive())
                                            <span class="badge text-bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge text-bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('update', $account)
                                            <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('delete', $account)
                                            <form action="{{ route('accounts.destroy', $account) }}" method="POST" class="d-inline" data-confirm-delete="Hapus account {{ $account->account_name }}?">
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
                <div class="mt-3">{{ $accounts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
