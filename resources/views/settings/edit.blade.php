<x-app-layout title="Settings">
    <x-page-header title="Pengaturan Sistem" description="Konfigurasi umum aplikasi." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <h2 class="h6 fw-semibold mb-3">Umum</h2>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <x-input-label for="app_name" value="Nama Aplikasi" />
                        <x-text-input id="app_name" name="app.name" type="text" :value="old('app.name', $settings->get('app.name'))" required />
                    </div>
                    <div class="col-12 col-md-3">
                        <x-input-label for="app_currency" value="Mata Uang" />
                        <x-text-input id="app_currency" name="app.currency" type="text" maxlength="3" :value="old('app.currency', $settings->get('app.currency'))" required />
                    </div>
                    <div class="col-12 col-md-3">
                        <x-input-label for="app_currency_symbol" value="Simbol" />
                        <x-text-input id="app_currency_symbol" name="app.currency_symbol" type="text" :value="old('app.currency_symbol', $settings->get('app.currency_symbol'))" required />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-input-label for="app_date_format" value="Format Tanggal" />
                        <select id="app_date_format" name="app.date_format" class="form-select">
                            @foreach (['d/m/Y' => '31/12/2026', 'Y-m-d' => '2026-12-31', 'd-m-Y' => '31-12-2026'] as $format => $example)
                                <option value="{{ $format }}" @selected(old('app.date_format', $settings->get('app.date_format')) === $format)>{{ $example }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <x-input-label for="app_timezone" value="Timezone" />
                        <x-text-input id="app_timezone" name="app.timezone" type="text" :value="old('app.timezone', $settings->get('app.timezone'))" required />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-input-label for="app_financial_year_start_month" value="Bulan Awal Tahun Fiskal" />
                        <select id="app_financial_year_start_month" name="app.financial_year_start_month" class="form-select">
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected((int) old('app.financial_year_start_month', $settings->get('app.financial_year_start_month')) === $month)>
                                    {{ \Carbon\Carbon::create(2000, $month, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h2 class="h6 fw-semibold mb-3">Transaksi</h2>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <x-input-label for="app_default_account_id" value="Account Default" />
                        <select id="app_default_account_id" name="app.default_account_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((int) old('app.default_account_id', $settings->get('app.default_account_id')) === $account->id)>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Digunakan sebagai pilihan awal pada form transaksi cepat.</div>
                    </div>
                </div>

                <h2 class="h6 fw-semibold mb-3">Notifikasi</h2>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="app.notifications_enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="app_notifications_enabled" name="app.notifications_enabled" value="1"
                            @checked(old('app.notifications_enabled', $settings->get('app.notifications_enabled')))>
                        <label class="form-check-label" for="app_notifications_enabled">Aktifkan notifikasi budget, hutang, dan piutang</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </div>
    </div>
</x-app-layout>
