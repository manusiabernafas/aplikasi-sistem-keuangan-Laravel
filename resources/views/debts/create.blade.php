<x-app-layout title="Tambah Hutang">
    <x-page-header title="Tambah Hutang" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('debts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-input-label for="creditor_name" value="Hutang Kepada" />
                        <x-text-input id="creditor_name" name="creditor_name" type="text" :value="old('creditor_name')" required autofocus />
                        <x-input-error :messages="$errors->get('creditor_name')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="amount" value="Nominal (Rp)" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="debt_date" value="Tanggal" />
                        <x-text-input id="debt_date" name="debt_date" type="date" :value="old('debt_date', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('debt_date')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="due_date" value="Jatuh Tempo" />
                        <x-text-input id="due_date" name="due_date" type="date" :value="old('due_date')" required />
                        <x-input-error :messages="$errors->get('due_date')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="account_id" value="Account Penerima (opsional)" />
                        <select id="account_id" name="account_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <x-input-label for="notes" value="Catatan (opsional)" />
                        <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('debts.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
