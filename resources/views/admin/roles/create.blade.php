<x-app-layout title="Tambah Role">
    <x-page-header title="Tambah Role" description="Buat role baru beserta permission-nya." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                @include('admin.roles._form', ['role' => null])

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
