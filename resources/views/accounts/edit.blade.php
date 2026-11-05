<x-app-layout title="Edit Account">
    <x-page-header title="Edit Account" description="Perbarui data account {{ $account->account_name }}." />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('accounts.update', $account) }}">
                @csrf
                @method('PUT')
                @include('accounts._form')
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
