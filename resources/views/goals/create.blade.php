<x-app-layout title="Tambah Financial Goal">
    <x-page-header title="Tambah Financial Goal" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('goals.store') }}">
                @csrf
                @include('goals._form', ['goal' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
