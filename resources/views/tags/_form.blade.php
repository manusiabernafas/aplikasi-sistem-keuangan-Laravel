<div class="row g-3">
    <div class="col-12 col-md-8">
        <x-input-label for="name" value="Nama Tag" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $tag?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-12 col-md-4">
        <x-input-label for="color" value="Warna (opsional)" />
        <input id="color" name="color" type="color" class="form-control form-control-color" value="{{ old('color', $tag?->color ?? '#64748B') }}">
    </div>
</div>
