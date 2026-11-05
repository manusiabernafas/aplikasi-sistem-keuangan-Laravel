<div class="row g-3">
    <div class="col-12">
        <x-input-label for="goal_name" value="Nama Target" />
        <x-text-input id="goal_name" name="goal_name" type="text" :value="old('goal_name', $goal?->goal_name)" required autofocus placeholder="Contoh: Dana Darurat" />
        <x-input-error :messages="$errors->get('goal_name')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="target_amount" value="Target Nominal (Rp)" />
        <x-text-input id="target_amount" name="target_amount" type="number" step="0.01" min="0.01" :value="old('target_amount', $goal?->target_amount)" required />
        <x-input-error :messages="$errors->get('target_amount')" />
    </div>

    @unless ($goal)
        <div class="col-12 col-md-6">
            <x-input-label for="current_amount" value="Sudah Terkumpul (opsional)" />
            <x-text-input id="current_amount" name="current_amount" type="number" step="0.01" min="0" :value="old('current_amount', 0)" />
        </div>
    @endunless

    <div class="col-12 col-md-6">
        <x-input-label for="target_date" value="Target Tanggal (opsional)" />
        <x-text-input id="target_date" name="target_date" type="date" :value="old('target_date', $goal?->target_date?->toDateString())" />
        <x-input-error :messages="$errors->get('target_date')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="account_id" value="Account Terkait (opsional)" />
        <select id="account_id" name="account_id" class="form-select">
            <option value="">—</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected((int) old('account_id', $goal?->account_id) === $account->id)>{{ $account->account_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <x-input-label for="description" value="Deskripsi (opsional)" />
        <textarea id="description" name="description" rows="2" class="form-control">{{ old('description', $goal?->description) }}</textarea>
    </div>
</div>
