<x-app-layout title="Audit Log">
    <x-page-header title="Audit Log" description="Jejak seluruh aktivitas penting dalam sistem." />

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <select name="module" class="form-select form-select-sm">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Semua Aksi</option>
                        <option value="created" @selected(request('action') === 'created')>Created</option>
                        <option value="updated" @selected(request('action') === 'updated')>Updated</option>
                        <option value="deleted" @selected(request('action') === 'deleted')>Deleted</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Semua User</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>

            @if ($logs->isEmpty())
                <x-empty-state icon="bi-clock-history" title="Belum ada aktivitas tercatat" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Modul</th>
                                <th>Record</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-nowrap small">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $log->user?->name ?? 'System' }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'deleted' ? 'danger' : 'info') }}-subtle">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($log->module) }}</td>
                                    <td>#{{ $log->record_id }}</td>
                                    <td class="text-secondary small">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
