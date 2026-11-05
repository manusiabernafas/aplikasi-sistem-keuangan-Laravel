<x-app-layout title="Edit Tag">
    <x-page-header title="Edit Tag" />

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('tags.update', $tag) }}">
                @csrf
                @method('PUT')
                @include('tags._form')
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('tags.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
