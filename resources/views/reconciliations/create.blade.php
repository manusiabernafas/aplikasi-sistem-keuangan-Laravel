<x-app-layout title="Mulai Rekonsiliasi">
    <x-page-header title="Mulai Rekonsiliasi" description="Pilih account dan masukkan saldo dari rekening koran/bank statement." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('reconciliations.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <x-input-label for="account_id" value="Account" />
                        <select id="account_id" name="account_id" class="form-select" required>
                            <option value="">— Pilih Account —</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((int) old('account_id') === $account->id)>
                                    {{ $account->account_name }} (Sistem: Rp {{ number_format($account->current_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('account_id')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="statement_date" value="Tanggal Statement" />
                        <x-text-input id="statement_date" name="statement_date" type="date" :value="old('statement_date', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('statement_date')" />
                    </div>
                    <div class="col-12 col-md-6">
                        <x-input-label for="statement_balance" value="Saldo Statement (Rp)" />
                        <x-text-input id="statement_balance" name="statement_balance" type="number" step="0.01" :value="old('statement_balance')" required />
                        <x-input-error :messages="$errors->get('statement_balance')" />
                    </div>
                    <div class="col-12">
                        <x-input-label for="notes" value="Catatan (opsional)" />
                        <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Mulai</button>
                    <a href="{{ route('reconciliations.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
