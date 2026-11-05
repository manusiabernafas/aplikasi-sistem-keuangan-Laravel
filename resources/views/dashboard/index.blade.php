<x-app-layout title="Dashboard">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-semibold mb-1">Halo, {{ Str::before(auth()->user()->name, ' ') }} 👋</h1>
            <p class="text-secondary small mb-0">{{ now()->translatedFormat('l, d F Y') }} &middot; Ringkasan periode: {{ $periodLabel }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2" id="periodForm">
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="today" @selected($period === 'today')>Hari ini</option>
                    <option value="week" @selected($period === 'week')>Minggu ini</option>
                    <option value="month" @selected($period === 'month')>Bulan ini</option>
                    <option value="last_month" @selected($period === 'last_month')>Bulan lalu</option>
                    <option value="year" @selected($period === 'year')>Tahun ini</option>
                    <option value="custom" @selected($period === 'custom')>Custom</option>
                </select>
                @if ($period === 'custom')
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Terapkan</button>
                @endif
            </form>

            @can('create', \App\Models\Transaction::class)
                <a href="{{ route('transactions.income.create') }}" class="btn btn-success btn-sm"><i class="bi bi-arrow-down-circle me-1"></i>Pemasukan</a>
                <a href="{{ route('transactions.expense.create') }}" class="btn btn-danger btn-sm"><i class="bi bi-arrow-up-circle me-1"></i>Pengeluaran</a>
                <a href="{{ route('transactions.transfer.create') }}" class="btn btn-info btn-sm text-white"><i class="bi bi-arrow-left-right me-1"></i>Transfer</a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <x-stat-card label="Total Saldo" value="Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}" icon="bi-wallet2" variant="primary" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Pemasukan ({{ $periodLabel }})" value="Rp {{ number_format($summary['income'], 0, ',', '.') }}" icon="bi-arrow-down-circle" variant="success" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Pengeluaran ({{ $periodLabel }})" value="Rp {{ number_format($summary['expense'], 0, ',', '.') }}" icon="bi-arrow-up-circle" variant="danger" />
        </div>
        <div class="col-6 col-xl-3">
            <x-stat-card label="Net Cash Flow" value="Rp {{ number_format($summary['net_cash_flow'], 0, ',', '.') }}" icon="bi-graph-up" :variant="$summary['net_cash_flow'] >= 0 ? 'success' : 'danger'" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Income vs Expense (6 Bulan Terakhir)</h2>
                    <canvas id="incomeExpenseChart" height="110"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Expense by Category</h2>
                    @if (empty($expenseByCategory['labels']))
                        <x-empty-state icon="bi-pie-chart" title="Belum ada data" description="Distribusi kategori pengeluaran akan tampil di sini." />
                    @else
                        <canvas id="expenseCategoryChart" height="200"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Transaksi Terbaru</h2>
                    @if ($recentTransactions->isEmpty())
                        <x-empty-state icon="bi-receipt" title="Belum ada transaksi" description="Mulai catat pemasukan atau pengeluaran pertama Anda.">
                            <x-slot name="action">
                                @can('create', \App\Models\Transaction::class)
                                    <a href="{{ route('transactions.income.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Transaksi
                                    </a>
                                @endcan
                            </x-slot>
                        </x-empty-state>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($recentTransactions as $transaction)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $transaction->badgeClass() }}">{{ $transaction->typeLabel() }}</span>
                                            <span class="small text-secondary">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="fw-medium">{{ $transaction->description ?: $transaction->category?->name ?? $transaction->transaction_number }}</div>
                                    </div>
                                    <div class="fw-semibold">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('transactions.index') }}" class="small text-decoration-none">Lihat semua transaksi <i class="bi bi-arrow-right"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Budget Progress</h2>
                    @if ($budgetsProgress->isEmpty())
                        <x-empty-state icon="bi-pie-chart-fill" title="Belum ada budget"
                            description="Buat anggaran bulanan untuk memantau pengeluaran Anda." />
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($budgetsProgress as $row)
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-medium">{{ $row['budget']->name }}</span>
                                        <span>{{ $row['progress']['percent'] }}%</span>
                                    </div>
                                    <div class="progress" style="height: .4rem;">
                                        <div class="progress-bar bg-{{ $row['progress']['level'] === 'exceeded' ? 'danger' : ($row['progress']['level'] === 'warning' ? 'warning' : 'success') }}"
                                            style="width: {{ min(100, $row['progress']['percent']) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('budgets.index') }}" class="small text-decoration-none">Lihat semua budget <i class="bi bi-arrow-right"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Hutang Jatuh Tempo</h2>
                    @if ($upcomingDebts->isEmpty())
                        <x-empty-state icon="bi-credit-card" title="Tidak ada data" />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($upcomingDebts as $debt)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium small">{{ $debt->creditor_name }}</div>
                                        <div class="text-secondary small">{{ $debt->due_date->format('d/m/Y') }}</div>
                                    </div>
                                    <span class="fw-semibold small">Rp {{ number_format($debt->remaining(), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Piutang Jatuh Tempo</h2>
                    @if ($upcomingReceivables->isEmpty())
                        <x-empty-state icon="bi-cash-coin" title="Tidak ada data" />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($upcomingReceivables as $receivable)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium small">{{ $receivable->debtor_name }}</div>
                                        <div class="text-secondary small">{{ $receivable->due_date->format('d/m/Y') }}</div>
                                    </div>
                                    <span class="fw-semibold small">Rp {{ number_format($receivable->remaining(), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">Financial Goals</h2>
                    @if ($goals->isEmpty())
                        <x-empty-state icon="bi-flag" title="Belum ada target" />
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach ($goals as $goal)
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-medium">{{ $goal->goal_name }}</span>
                                        <span>{{ $goal->progressPercent() }}%</span>
                                    </div>
                                    <div class="progress" style="height: .4rem;">
                                        <div class="progress-bar bg-primary" style="width: {{ $goal->progressPercent() }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script id="dashboardChartData" type="application/json">
        {!! json_encode([
            'trend' => $trend,
            'expenseByCategory' => $expenseByCategory,
        ]) !!}
    </script>
</x-app-layout>
