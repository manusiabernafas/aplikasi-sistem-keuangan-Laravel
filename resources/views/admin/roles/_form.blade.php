@php
    $selectedPermissionIds = old('permission_ids', $role?->permissions->pluck('id')->all() ?? []);
@endphp

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <x-input-label for="name" value="Nama Role" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $role?->name)" required autofocus :readonly="$role?->is_system ?? false" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="description" value="Deskripsi" />
        <x-text-input id="description" name="description" type="text" :value="old('description', $role?->description)" />
        <x-input-error :messages="$errors->get('description')" />
    </div>
</div>

<x-input-label value="Permission" />
<div class="row g-3 mt-1">
    @foreach ($permissions as $module => $items)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="fw-semibold small text-uppercase text-secondary mb-2">{{ str_replace('.', ' ', $module) }}</div>
                @foreach ($items as $permission)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                            id="permission-{{ $permission->id }}" @checked(in_array($permission->id, $selectedPermissionIds))>
                        <label class="form-check-label small" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
<x-input-error :messages="$errors->get('permission_ids')" class="mt-2" />
