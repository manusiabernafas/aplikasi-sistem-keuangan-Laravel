@php
    $user = auth()->user();
@endphp

<aside class="app-sidebar">
    <div class="d-flex align-items-center gap-2 px-3" style="height: 64px; border-bottom: 1px solid #E2E8F0;">
        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-bold" style="width:2.25rem;height:2.25rem;">K</span>
        <span class="fs-5 fw-semibold text-dark">{{ config('app.name', 'Keuang') }}</span>
    </div>

    <nav class="p-2 overflow-auto" style="height: calc(100vh - 64px);">
        <div class="nav flex-column">
            <x-sidebar-link :href="route('dashboard')" icon="bi-grid-1x2-fill" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-sidebar-link>
        </div>

        @if ($user->hasPermission('transaction.view'))
            <div class="nav-section-title">Keuangan</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('transactions.index')" icon="bi-list-ul" :active="request()->routeIs('transactions.index')">Semua Transaksi</x-sidebar-link>
                <x-sidebar-link :href="route('transactions.income.create')" icon="bi-arrow-down-circle" :active="request()->routeIs('transactions.income.*')">Pemasukan</x-sidebar-link>
                <x-sidebar-link :href="route('transactions.expense.create')" icon="bi-arrow-up-circle" :active="request()->routeIs('transactions.expense.*')">Pengeluaran</x-sidebar-link>
                <x-sidebar-link :href="route('transactions.transfer.create')" icon="bi-arrow-left-right" :active="request()->routeIs('transactions.transfer.*')">Transfer</x-sidebar-link>
                <x-sidebar-link :href="route('recurring-transactions.index')" icon="bi-arrow-repeat" :active="request()->routeIs('recurring-transactions.*')">Recurring</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('account.manage') || $user->hasPermission('category.manage'))
            <div class="nav-section-title">Master Data</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('accounts.index')" icon="bi-bank" :active="request()->routeIs('accounts.*')">Account</x-sidebar-link>
                <x-sidebar-link :href="route('categories.index')" icon="bi-tags" :active="request()->routeIs('categories.*')">Kategori</x-sidebar-link>
                <x-sidebar-link :href="route('tags.index')" icon="bi-tag" :active="request()->routeIs('tags.*')">Tag</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('budget.manage'))
            <div class="nav-section-title">Perencanaan</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('budgets.index')" icon="bi-pie-chart" :active="request()->routeIs('budgets.*')">Budget</x-sidebar-link>
                <x-sidebar-link :href="route('goals.index')" icon="bi-flag" :active="request()->routeIs('goals.*')">Financial Goals</x-sidebar-link>
                <x-sidebar-link :href="route('recurring-transactions.index')" icon="bi-arrow-repeat" :active="request()->routeIs('recurring-transactions.*')">Recurring</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('debt.manage') || $user->hasPermission('receivable.manage'))
            <div class="nav-section-title">Hutang & Piutang</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('debts.index')" icon="bi-credit-card" :active="request()->routeIs('debts.*')">Hutang</x-sidebar-link>
                <x-sidebar-link :href="route('receivables.index')" icon="bi-cash-coin" :active="request()->routeIs('receivables.*')">Piutang</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('reconciliation.manage'))
            <div class="nav-section-title">&nbsp;</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('reconciliations.index')" icon="bi-check2-square" :active="request()->routeIs('reconciliations.*')">Rekonsiliasi</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('report.view'))
            <div class="nav-section-title">Laporan</div>
            <div class="nav flex-column">
                <x-sidebar-link :href="route('reports.cash-flow')" icon="bi-graph-up" :active="request()->routeIs('reports.cash-flow')">Cash Flow</x-sidebar-link>
                <x-sidebar-link :href="route('reports.income')" icon="bi-graph-up-arrow" :active="request()->routeIs('reports.income')">Income</x-sidebar-link>
                <x-sidebar-link :href="route('reports.expense')" icon="bi-graph-down-arrow" :active="request()->routeIs('reports.expense')">Expense</x-sidebar-link>
                <x-sidebar-link :href="route('reports.profit-loss')" icon="bi-file-earmark-bar-graph" :active="request()->routeIs('reports.profit-loss')">Profit & Loss</x-sidebar-link>
                <x-sidebar-link :href="route('reports.net-worth')" icon="bi-gem" :active="request()->routeIs('reports.net-worth')">Net Worth</x-sidebar-link>
                <x-sidebar-link :href="route('reports.summary')" icon="bi-file-earmark-text" :active="request()->routeIs('reports.summary')">Financial Summary</x-sidebar-link>
                <x-sidebar-link :href="route('insights.index')" icon="bi-lightbulb" :active="request()->routeIs('insights.index')">Financial Insights</x-sidebar-link>
            </div>
        @endif

        @if ($user->hasPermission('user.manage') || $user->hasPermission('audit.view') || $user->hasPermission('setting.manage') || $user->hasPermission('backup.manage'))
            <div class="nav-section-title">System</div>
            <div class="nav flex-column">
                @if ($user->hasPermission('user.manage'))
                    <x-sidebar-link :href="route('admin.users.index')" icon="bi-people" :active="request()->routeIs('admin.users.*')">Users</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.roles.index')" icon="bi-shield-lock" :active="request()->routeIs('admin.roles.*')">Roles & Permissions</x-sidebar-link>
                @endif
                @if ($user->hasPermission('audit.view'))
                    <x-sidebar-link :href="route('audit-logs.index')" icon="bi-clock-history" :active="request()->routeIs('audit-logs.*')">Audit Logs</x-sidebar-link>
                @endif
                @if ($user->hasPermission('backup.manage'))
                    <x-sidebar-link :href="route('backups.index')" icon="bi-database" :active="request()->routeIs('backups.*')">Backup</x-sidebar-link>
                @endif
                @if ($user->hasPermission('setting.manage'))
                    <x-sidebar-link :href="route('settings.edit')" icon="bi-gear" :active="request()->routeIs('settings.*')">Settings</x-sidebar-link>
                @endif
            </div>
        @endif
    </nav>
</aside>
