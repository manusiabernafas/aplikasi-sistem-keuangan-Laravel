<div class="row g-3">
    <div class="col-12 col-md-4">
        <x-input-label for="account_code" value="Kode Account" />
        <x-text-input id="account_code" name="account_code" type="text" :value="old('account_code', $account?->account_code)" required autofocus />
        <x-input-error :messages="$errors->get('account_code')" />
    </div>

    <div class="col-12 col-md-8">
        <x-input-label for="account_name" value="Nama Account" />
        <x-text-input id="account_name" name="account_name" type="text" :value="old('account_name', $account?->account_name)" required />
        <x-input-error :messages="$errors->get('account_name')" />
    </div>

    <div class="col-12 col-md-4">
        <x-input-label for="account_type_id" value="Tipe Account" />
        <select id="account_type_id" name="account_type_id" class="form-select" required>
            @foreach ($accountTypes as $type)
                <option value="{{ $type->id }}" @selected((int) old('account_type_id', $account?->account_type_id) === $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('account_type_id')" />
    </div>

    <div class="col-12 col-md-4">
        <x-input-label for="opening_balance" value="Saldo Awal" />
        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" :value="old('opening_balance', $account?->opening_balance ?? 0)" required />
        <x-input-error :messages="$errors->get('opening_balance')" />
        @if ($account)
            <div class="form-text">Saldo berjalan saat ini: Rp {{ number_format($account->current_balance, 0, ',', '.') }}</div>
        @endif
    </div>

    <div class="col-12 col-md-4">
        <x-input-label for="currency" value="Mata Uang" />
        <x-text-input id="currency" name="currency" type="text" maxlength="3" :value="old('currency', $account?->currency ?? 'IDR')" />
        <x-input-error :messages="$errors->get('currency')" />
    </div>

    <div class="col-12">
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="2" class="form-control">{{ old('description', $account?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="INACTIVE">
            <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="ACTIVE"
                @checked(old('status', $account?->status ?? 'ACTIVE') === 'ACTIVE')>
            <label class="form-check-label" for="status">Account aktif</label>
        </div>
    </div>
</div>
