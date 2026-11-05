<x-app-layout title="Tag">
    <x-page-header title="Tag" description="Kelola tag untuk pengelompokan transaksi bebas.">
        <x-slot name="actions">
            @can('create', \App\Models\Tag::class)
                <a href="{{ route('tags.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Tag
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($tags->isEmpty())
                <x-empty-state icon="bi-tag" title="Belum ada tag" description="Buat tag seperti #kantor atau #urgent untuk mengelompokkan transaksi." />
            @else
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($tags as $tag)
                        <span class="badge rounded-pill text-bg-light border d-inline-flex align-items-center gap-2 py-2 px-3">
                            <span>#{{ $tag->name }}</span>
                            <span class="text-secondary small">({{ $tag->transactions_count }})</span>
                            @can('update', $tag)
                                <a href="{{ route('tags.edit', $tag) }}" class="text-decoration-none"><i class="bi bi-pencil small"></i></a>
                            @endcan
                            @can('delete', $tag)
                                <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="d-inline" data-confirm-delete="Hapus tag {{ $tag->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0"><i class="bi bi-x-lg small"></i></button>
                                </form>
                            @endcan
                        </span>
                    @endforeach
                </div>
                {{ $tags->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
