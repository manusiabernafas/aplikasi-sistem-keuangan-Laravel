<x-app-layout title="Kategori">
    <x-page-header title="Kategori" description="Kelola kategori pemasukan dan pengeluaran, mendukung subkategori.">
        <x-slot name="actions">
            @can('create', \App\Models\Category::class)
                <a href="{{ route('categories.create', ['category_type_id' => $categoryTypes->firstWhere('code', $activeType)?->id]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
                </a>
            @endcan
        </x-slot>
    </x-page-header>

    <ul class="nav nav-pills mb-3">
        @foreach ($categoryTypes as $type)
            <li class="nav-item">
                <a class="nav-link {{ $activeType === $type->code ? 'active' : '' }}" href="{{ route('categories.index', ['type' => $type->code]) }}">
                    {{ $type->name }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($tree->isEmpty())
                <x-empty-state icon="bi-tags" title="Belum ada kategori" description="Tambahkan kategori pertama untuk tipe ini." />
            @else
                <div class="list-group list-group-flush">
                    @foreach ($tree as $category)
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi {{ $category->icon ?: 'bi-folder2' }}"></i>
                                    <span class="fw-medium">{{ $category->name }}</span>
                                    @unless ($category->is_active)
                                        <span class="badge text-bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                    @endunless
                                </div>
                                <div>
                                    @can('update', $category)
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('delete', $category)
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" data-confirm-delete="Hapus kategori {{ $category->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>

                            @if ($category->children->isNotEmpty())
                                <div class="ms-4 mt-2 d-flex flex-column gap-2">
                                    @foreach ($category->children as $child)
                                        <div class="d-flex align-items-center justify-content-between border-start ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-arrow-return-right text-secondary"></i>
                                                <span>{{ $child->name }}</span>
                                                @unless ($child->is_active)
                                                    <span class="badge text-bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                                                @endunless
                                            </div>
                                            <div>
                                                @can('update', $child)
                                                    <a href="{{ route('categories.edit', $child) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                                @endcan
                                                @can('delete', $child)
                                                    <form action="{{ route('categories.destroy', $child) }}" method="POST" class="d-inline" data-confirm-delete="Hapus kategori {{ $child->name }}?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
