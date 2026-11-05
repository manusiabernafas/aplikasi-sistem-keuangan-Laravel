<x-app-layout title="Tambah Kategori">
    <x-page-header title="Tambah Kategori" description="Buat kategori pemasukan atau pengeluaran baru." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                @include('categories._form', ['category' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
