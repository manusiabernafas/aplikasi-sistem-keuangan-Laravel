<x-app-layout title="Users">
    <x-page-header title="Manajemen User" description="Kelola akun pengguna dan peran akses mereka.">
        <x-slot name="actions">
            @can('create', \App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah User
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari nama atau email...">
                </div>
                <div class="col-6 col-md-3">
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->slug }}" @selected(request('role') === $role->slug)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>

            @if ($users->isEmpty())
                <x-empty-state icon="bi-people" title="Belum ada user"
                    description="Tambahkan user pertama untuk mulai mengelola akses aplikasi.">
                    <x-slot name="action">
                        @can('create', \App\Models\User::class)
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Tambah User</a>
                        @endcan
                    </x-slot>
                </x-empty-state>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="fw-medium">{{ $user->name }}</td>
                                    <td class="text-secondary">{{ $user->email }}</td>
                                    <td>
                                        @forelse ($user->roles as $role)
                                            <span class="badge text-bg-light border me-1">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-secondary small">&mdash;</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if ($user->is_active)
                                            <span class="badge text-bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge text-bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @can('update', $user)
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $user)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" data-confirm-delete="Hapus user {{ $user->name }}?">
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
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
