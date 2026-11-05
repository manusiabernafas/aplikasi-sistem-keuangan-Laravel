@php
    $incomeCategories = \App\Models\Category::ofType('INCOME')->active()->with('parent')->orderBy('name')->get();
    $expenseCategories = \App\Models\Category::ofType('EXPENSE')->active()->with('parent')->orderBy('name')->get();
    $accounts = \App\Models\Account::active()->orderBy('account_name')->get();
@endphp

<x-modal name="quick-transaction-modal" max-width="md">
    <div class="modal-header">
        <h2 class="h6 fw-semibold mb-0">Transaksi Cepat</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <ul class="nav nav-pills nav-fill mb-3" id="quickTxTabs">
            <li class="nav-item">
                <button class="nav-link active" data-quick-tab="INCOME" type="button">Pemasukan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-quick-tab="EXPENSE" type="button">Pengeluaran</button>
            </li>
        </ul>

        <form id="quickTransactionForm">
            @csrf
            <input type="hidden" name="transaction_type" value="INCOME" id="quick_transaction_type">

            <div class="mb-3">
                <x-input-label for="quick_transaction_date" value="Tanggal" />
                <input type="date" id="quick_transaction_date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>

            <div class="mb-3">
                <x-input-label for="quick_amount" value="Nominal (Rp)" />
                <input type="number" id="quick_amount" name="amount" step="0.01" min="0.01" class="form-control" required>
            </div>

            <div class="mb-3">
                <x-input-label for="quick_account_id" value="Account" />
                <select id="quick_account_id" name="account_id" class="form-select" required>
                    <option value="">— Pilih Account —</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <x-input-label for="quick_category_id" value="Kategori" />
                <select id="quick_category_id" name="category_id" class="form-select" required
                    data-income-options='@json($incomeCategories->map(fn ($c) => ["id" => $c->id, "name" => $c->fullName()]))'
                    data-expense-options='@json($expenseCategories->map(fn ($c) => ["id" => $c->id, "name" => $c->fullName()]))'>
                    @foreach ($incomeCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->fullName() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-1">
                <x-input-label for="quick_description" value="Deskripsi (opsional)" />
                <input type="text" id="quick_description" name="description" class="form-control">
            </div>

            <div class="text-danger small mt-2 d-none" id="quickTransactionError"></div>
        </form>
    </div>
    <div class="modal-footer">
        <x-secondary-button data-bs-dismiss="modal">Batal</x-secondary-button>
        <button type="button" class="btn btn-primary" id="quickTransactionSubmit">Simpan</button>
    </div>
</x-modal>
