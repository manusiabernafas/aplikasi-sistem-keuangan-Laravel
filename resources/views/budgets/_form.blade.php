<div class="row g-3">
    <div class="col-12 col-md-6">
        <x-input-label for="name" value="Nama Budget" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $budget?->name)" required autofocus placeholder="Contoh: Budget Makanan September" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="period_type" value="Periode" />
        <select id="period_type" name="period_type" class="form-select" required>
            <option value="MONTHLY" @selected(old('period_type', $budget?->period_type ?? 'MONTHLY') === 'MONTHLY')>Bulanan</option>
            <option value="ANNUAL" @selected(old('period_type', $budget?->period_type) === 'ANNUAL')>Tahunan</option>
        </select>
        <x-input-error :messages="$errors->get('period_type')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="category_id" value="Kategori (opsional)" />
        <select id="category_id" name="category_id" class="form-select">
            <option value="">— Semua Kategori —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $budget?->category_id) === $category->id)>{{ $category->fullName() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="account_id" value="Account (opsional)" />
        <select id="account_id" name="account_id" class="form-select">
            <option value="">— Semua Account —</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected((int) old('account_id', $budget?->account_id) === $account->id)>{{ $account->account_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('account_id')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="amount" value="Nominal Budget (Rp)" />
        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount', $budget?->amount)" required />
        <x-input-error :messages="$errors->get('amount')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="alert_threshold_percent" value="Alert Threshold (%)" />
        <x-text-input id="alert_threshold_percent" name="alert_threshold_percent" type="number" min="1" max="100" :value="old('alert_threshold_percent', $budget?->alert_threshold_percent ?? 80)" />
        <div class="form-text">Peringatan akan muncul saat pemakaian melewati persentase ini.</div>
    </div>

    @if ($budget)
        <div class="col-12">
            <div class="form-check form-switch">
                <input type="hidden" name="status" value="INACTIVE">
                <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="ACTIVE"
                    @checked(old('status', $budget->status) === 'ACTIVE')>
                <label class="form-check-label" for="status">Budget aktif</label>
            </div>
        </div>
    @endif
</div>
