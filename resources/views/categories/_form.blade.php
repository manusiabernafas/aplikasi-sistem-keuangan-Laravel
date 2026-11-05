<div class="row g-3">
    <div class="col-12 col-md-6">
        <x-input-label for="category_type_id" value="Tipe Kategori" />
        <select id="category_type_id" name="category_type_id" class="form-select" required @disabled($category !== null)>
            @foreach ($categoryTypes as $type)
                <option value="{{ $type->id }}" @selected((int) old('category_type_id', $category?->category_type_id ?? $selectedType ?? 0) === $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        @if ($category)
            <input type="hidden" name="category_type_id" value="{{ $category->category_type_id }}">
            <div class="form-text">Tipe kategori tidak dapat diubah setelah dibuat.</div>
        @endif
        <x-input-error :messages="$errors->get('category_type_id')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="parent_id" value="Induk Kategori (opsional)" />
        <select id="parent_id" name="parent_id" class="form-select">
            <option value="">— Kategori Utama —</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category?->parent_id) === $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('parent_id')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="name" value="Nama Kategori" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $category?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-12 col-md-3">
        <x-input-label for="icon" value="Icon (bootstrap-icons, opsional)" />
        <x-text-input id="icon" name="icon" type="text" placeholder="bi-cup-hot" :value="old('icon', $category?->icon)" />
    </div>

    <div class="col-12 col-md-3">
        <x-input-label for="color" value="Warna (opsional)" />
        <input id="color" name="color" type="color" class="form-control form-control-color" value="{{ old('color', $category?->color ?? '#4338CA') }}">
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                @checked(old('is_active', $category?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Kategori aktif</label>
        </div>
    </div>
</div>
