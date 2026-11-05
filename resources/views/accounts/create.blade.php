<x-app-layout title="Tambah Account">
    <x-page-header title="Tambah Account" description="Buat account kas, bank, atau e-wallet baru." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf
                @include('accounts._form', ['account' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
