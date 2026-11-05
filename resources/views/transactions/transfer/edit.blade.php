<x-app-layout title="Edit Transfer">
    <x-page-header title="Edit Transfer" :description="$transaction->transaction_number" />

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <x-input-label for="transaction_date" value="Tanggal" />
                                <x-text-input id="transaction_date" name="transaction_date" type="date" :value="old('transaction_date', $transaction->transaction_date->toDateString())" required />
                                <x-input-error :messages="$errors->get('transaction_date')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="amount" value="Nominal (Rp)" />
                                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount', $transaction->amount)" required />
                                <x-input-error :messages="$errors->get('amount')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="account_id" value="Dari Account" />
                                <select id="account_id" name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id', $transaction->account_id) === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('account_id')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="destination_account_id" value="Ke Account" />
                                <select id="destination_account_id" name="destination_account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('destination_account_id', $transaction->destination_account_id) === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('destination_account_id')" />
                            </div>

                            <div class="col-12">
                                <x-input-label for="description" value="Deskripsi (opsional)" />
                                <x-text-input id="description" name="description" type="text" :value="old('description', $transaction->description)" />
                            </div>

                            <div class="col-12">
                                <x-input-label for="notes" value="Catatan (opsional)" />
                                <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes', $transaction->notes) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
