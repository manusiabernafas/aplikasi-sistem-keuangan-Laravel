<x-app-layout title="Tambah Recurring">
    <x-page-header title="Tambah Transaksi Berulang" />

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('recurring-transactions.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <x-input-label for="name" value="Nama" />
                                <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus placeholder="Contoh: Gaji Bulanan" />
                                <x-input-error :messages="$errors->get('name')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="transaction_type" value="Tipe Transaksi" />
                                <select id="transaction_type" name="transaction_type" class="form-select" required onchange="toggleRecurringFields()">
                                    <option value="INCOME" @selected(old('transaction_type') === 'INCOME')>Pemasukan</option>
                                    <option value="EXPENSE" @selected(old('transaction_type', 'EXPENSE') === 'EXPENSE')>Pengeluaran</option>
                                    <option value="TRANSFER" @selected(old('transaction_type') === 'TRANSFER')>Transfer</option>
                                </select>
                                <x-input-error :messages="$errors->get('transaction_type')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="amount" value="Nominal (Rp)" />
                                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
                                <x-input-error :messages="$errors->get('amount')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="account_id" value="Account" />
                                <select id="account_id" name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('account_id')" />
                            </div>

                            <div class="col-12 col-md-6" id="destinationAccountField">
                                <x-input-label for="destination_account_id" value="Ke Account (khusus Transfer)" />
                                <select id="destination_account_id" name="destination_account_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('destination_account_id') === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('destination_account_id')" />
                            </div>

                            <div class="col-12 col-md-6" id="categoryField">
                                <x-input-label for="category_id" value="Kategori" />
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" data-type="{{ $category->categoryType->code }}" @selected((int) old('category_id') === $category->id)>
                                            [{{ $category->categoryType->code }}] {{ $category->fullName() }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="frequency" value="Frekuensi" />
                                <select id="frequency" name="frequency" class="form-select" required onchange="toggleRecurringFields()">
                                    <option value="DAILY" @selected(old('frequency') === 'DAILY')>Harian</option>
                                    <option value="WEEKLY" @selected(old('frequency') === 'WEEKLY')>Mingguan</option>
                                    <option value="MONTHLY" @selected(old('frequency', 'MONTHLY') === 'MONTHLY')>Bulanan</option>
                                    <option value="YEARLY" @selected(old('frequency') === 'YEARLY')>Tahunan</option>
                                </select>
                                <x-input-error :messages="$errors->get('frequency')" />
                            </div>

                            <div class="col-12 col-md-3">
                                <x-input-label for="interval_count" value="Setiap" />
                                <x-text-input id="interval_count" name="interval_count" type="number" min="1" max="365" :value="old('interval_count', 1)" />
                            </div>

                            <div class="col-12 col-md-3" id="dayOfMonthField">
                                <x-input-label for="day_of_month" value="Tanggal" />
                                <x-text-input id="day_of_month" name="day_of_month" type="number" min="1" max="31" :value="old('day_of_month')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="start_date" value="Mulai Tanggal" />
                                <x-text-input id="start_date" name="start_date" type="date" :value="old('start_date', now()->toDateString())" required />
                                <x-input-error :messages="$errors->get('start_date')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="end_date" value="Berakhir Tanggal (opsional)" />
                                <x-text-input id="end_date" name="end_date" type="date" :value="old('end_date')" />
                                <x-input-error :messages="$errors->get('end_date')" />
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('recurring-transactions.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRecurringFields() {
            const type = document.getElementById('transaction_type').value;
            document.getElementById('destinationAccountField').style.display = type === 'TRANSFER' ? '' : 'none';
            document.getElementById('categoryField').style.display = type === 'TRANSFER' ? 'none' : '';

            const frequency = document.getElementById('frequency').value;
            document.getElementById('dayOfMonthField').style.display = frequency === 'MONTHLY' ? '' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleRecurringFields);
    </script>
</x-app-layout>
