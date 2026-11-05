<x-app-layout title="Tambah Piutang">
    <x-page-header title="Tambah Piutang" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('receivables.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-input-label for="debtor_name" value="Piutang Kepada" />
                        <x-text-input id="debtor_name" name="debtor_name" type="text" :value="old('debtor_name')" required autofocus />
                        <x-input-error :messages="$errors->get('debtor_name')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="amount" value="Nominal (Rp)" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="receivable_date" value="Tanggal" />
                        <x-text-input id="receivable_date" name="receivable_date" type="date" :value="old('receivable_date', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('receivable_date')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="due_date" value="Jatuh Tempo" />
                        <x-text-input id="due_date" name="due_date" type="date" :value="old('due_date')" required />
                        <x-input-error :messages="$errors->get('due_date')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="account_id" value="Account Sumber Dana (opsional)" />
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
                    <a href="{{ route('receivables.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
