@php
    $isIncome = $transaction->transaction_type === 'INCOME';
    $selectedTagIds = old('tag_ids', $transaction->tags->pluck('id')->all());
@endphp
<x-app-layout title="Edit Transaksi">
    <x-page-header :title="'Edit ' . $transaction->typeLabel()" :description="$transaction->transaction_number" />

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
                                <x-input-label for="account_id" :value="$isIncome ? 'Account Tujuan' : 'Account Sumber'" />
                                <select id="account_id" name="account_id" class="form-select" required>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id', $transaction->account_id) === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('account_id')" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="category_id" value="Kategori" />
                                <select id="category_id" name="category_id" class="form-select" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $transaction->category_id) === $category->id)>{{ $category->fullName() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" />
                            </div>

                            <div class="col-12">
                                <x-input-label for="description" value="Deskripsi" />
                                <x-text-input id="description" name="description" type="text" :value="old('description', $transaction->description)" />
                            </div>

                            <div class="col-12 col-md-6">
                                <x-input-label for="reference_number" value="Nomor Referensi (opsional)" />
                                <x-text-input id="reference_number" name="reference_number" type="text" :value="old('reference_number', $transaction->reference_number)" />
                            </div>

                            @if ($tags->isNotEmpty())
                                <div class="col-12">
                                    <x-input-label value="Tag (opsional)" />
                                    <div class="d-flex flex-wrap gap-3 mt-1">
                                        @foreach ($tags as $tag)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}" @checked(in_array($tag->id, $selectedTagIds))>
                                                <label class="form-check-label" for="tag-{{ $tag->id }}">#{{ $tag->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

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
