<x-app-layout title="Tambah Budget">
    <x-page-header title="Tambah Budget" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('budgets.store') }}">
                @csrf
                @include('budgets._form', ['budget' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
