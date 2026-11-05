@php
    $isTransfer = $recurring->transaction_type === 'TRANSFER';
@endphp
<x-app-layout title="Edit Recurring">
    <x-page-header title="Edit Transaksi Berulang" :description="$recurring->name" />

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('recurring-transactions.update', $recurring) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <x-input-label for="name" value="Nama" />
                                <x-text-input id="name" name="name" type="text" :value="old('name', $recurring->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label value="Tipe Transaksi" />
                                <input type="text" class="form-control" value="{{ $recurring->transaction_type }}" disabled>
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="amount" value="Nominal (Rp)" />
                                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount', $recurring->amount)" required />
                                <x-input-error :messages="$errors->get('amount')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="account_id" value="Account" />
                                <select id="account_id" name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id', $recurring->account_id) === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('account_id')" />
                            </div>

                            @if ($isTransfer)
                                <div class="col-12 col-md-6">
                                    <x-input-label for="destination_account_id" value="Ke Account" />
                                    <select id="destination_account_id" name="destination_account_id" class="form-select">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((int) old('destination_account_id', $recurring->destination_account_id) === $account->id)>{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('destination_account_id')" />
                                </div>
                            @else
                                <div class="col-12 col-md-6">
                                    <x-input-label for="category_id" value="Kategori" />
                                    <select id="category_id" name="category_id" class="form-select">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected((int) old('category_id', $recurring->category_id) === $category->id)>{{ $category->fullName() }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('category_id')" />
                                </div>
                            @endif

                            <div class="col-12 col-md-6">
                                <x-input-label for="frequency" value="Frekuensi" />
                                <select id="frequency" name="frequency" class="form-select" required>
                                    <option value="DAILY" @selected(old('frequency', $recurring->frequency) === 'DAILY')>Harian</option>
                                    <option value="WEEKLY" @selected(old('frequency', $recurring->frequency) === 'WEEKLY')>Mingguan</option>
                                    <option value="MONTHLY" @selected(old('frequency', $recurring->frequency) === 'MONTHLY')>Bulanan</option>
                                    <option value="YEARLY" @selected(old('frequency', $recurring->frequency) === 'YEARLY')>Tahunan</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <x-input-label for="interval_count" value="Setiap" />
                                <x-text-input id="interval_count" name="interval_count" type="number" min="1" max="365" :value="old('interval_count', $recurring->interval_count)" />
                            </div>

                            <div class="col-12 col-md-3">
                                <x-input-label for="day_of_month" value="Tanggal" />
                                <x-text-input id="day_of_month" name="day_of_month" type="number" min="1" max="31" :value="old('day_of_month', $recurring->day_of_month)" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="end_date" value="Berakhir Tanggal (opsional)" />
                                <x-text-input id="end_date" name="end_date" type="date" :value="old('end_date', $recurring->end_date?->toDateString())" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="form-select" required>
                                    <option value="ACTIVE" @selected(old('status', $recurring->status) === 'ACTIVE')>Aktif</option>
                                    <option value="PAUSED" @selected(old('status', $recurring->status) === 'PAUSED')>Dijeda</option>
                                    <option value="COMPLETED" @selected(old('status', $recurring->status) === 'COMPLETED')>Selesai</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-secondary small mt-3">
                            Next run: {{ $recurring->next_run_date->format('d/m/Y') }}
                            @if ($recurring->last_run_date) &middot; Last run: {{ $recurring->last_run_date->format('d/m/Y') }} @endif
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('recurring-transactions.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
