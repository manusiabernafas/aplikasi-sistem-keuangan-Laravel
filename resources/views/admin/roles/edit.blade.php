<x-app-layout title="Edit Role">
    <x-page-header title="Edit Role" description="Perbarui permission untuk role {{ $role->name }}." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            @if ($role->is_system)
                <div class="alert alert-warning small">
                    Role sistem tidak dapat diubah nama atau dihapus, namun permission tetap dapat disesuaikan.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PUT')
                @include('admin.roles._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
