<x-app-layout title="Roles & Permissions">
    <x-page-header title="Roles & Permissions" description="Kelola peran dan hak akses pengguna.">
        <x-slot name="actions">
            @can('create', \App\Models\Role::class)
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Role
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari role...">
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>

            @if ($roles->isEmpty())
                <x-empty-state icon="bi-shield-lock" title="Belum ada role" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Role</th>
                                <th>Deskripsi</th>
                                <th>Jumlah User</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="fw-medium">
                                        {{ $role->name }}
                                        @if ($role->is_system)
                                            <span class="badge text-bg-light border ms-1">System</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ $role->description ?: '—' }}</td>
                                    <td>{{ $role->users_count }}</td>
                                    <td class="text-end">
                                        @can('update', $role)
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $role)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" data-confirm-delete="Hapus role {{ $role->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
