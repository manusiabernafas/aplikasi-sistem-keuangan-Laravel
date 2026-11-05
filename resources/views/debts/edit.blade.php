<x-app-layout title="Edit Hutang">
    <x-page-header title="Edit Hutang" :description="$debt->debt_number" />

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('debts.update', $debt) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <x-input-label for="creditor_name" value="Hutang Kepada" />
                                <x-text-input id="creditor_name" name="creditor_name" type="text" :value="old('creditor_name', $debt->creditor_name)" required />
                                <x-input-error :messages="$errors->get('creditor_name')" />
                            </div>
                            <div class="col-12 col-md-6">
                                <x-input-label for="amount" value="Nominal (Rp)" />
                                <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" :value="old('amount', $debt->amount)" required />
                                <x-input-error :messages="$errors->get('amount')" />
                            </div>
                            <div class="col-12 col-md-6">
                                <x-input-label for="account_id" value="Account Penerima" />
                                <select id="account_id" name="account_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}" @selected((int) old('account_id', $debt->account_id) === $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <x-input-label for="debt_date" value="Tanggal" />
                                <x-text-input id="debt_date" name="debt_date" type="date" :value="old('debt_date', $debt->debt_date->toDateString())" required />
                            </div>
                            <div class="col-12 col-md-6">
                                <x-input-label for="due_date" value="Jatuh Tempo" />
                                <x-text-input id="due_date" name="due_date" type="date" :value="old('due_date', $debt->due_date->toDateString())" required />
                            </div>
                            <div class="col-12">
                                <x-input-label for="notes" value="Catatan (opsional)" />
                                <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes', $debt->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('debts.index') }}" class="btn btn-outline-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Total Hutang</span>
                        <span class="fw-semibold">Rp {{ number_format($debt->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Total Pembayaran</span>
                        <span class="fw-semibold text-success">Rp {{ number_format($debt->totalPaid(), 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Sisa Hutang</span>
                        <span class="fw-semibold text-danger">Rp {{ number_format($debt->remaining(), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if ($debt->remaining() > 0)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 fw-semibold mb-3">Catat Pembayaran</h2>
                        <form method="POST" action="{{ route('debts.pay', $debt) }}">
                            @csrf
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <x-input-label for="payment_date" value="Tanggal" />
                                    <x-text-input id="payment_date" name="payment_date" type="date" :value="now()->toDateString()" required />
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-input-label for="pay_amount" value="Nominal" />
                                    <input type="number" step="0.01" min="0.01" max="{{ $debt->remaining() }}" name="amount" id="pay_amount" class="form-control" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <x-input-label for="pay_account_id" value="Dari Account" />
                                    <select id="pay_account_id" name="account_id" class="form-select" required>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan Pembayaran</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Riwayat Pembayaran</h2>
                    @if ($debt->payments->isEmpty())
                        <x-empty-state icon="bi-receipt" title="Belum ada pembayaran" />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($debt->payments as $payment)
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="small text-secondary">{{ $payment->payment_date->format('d/m/Y') }}</span>
                                    <span class="fw-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
