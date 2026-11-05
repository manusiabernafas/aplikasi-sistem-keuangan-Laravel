@php
    $selectedRoleIds = old('role_ids', $user?->roles->pluck('id')->all() ?? []);
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6">
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="password" :value="$user ? 'Password Baru (opsional)' : 'Password'" />
        <x-text-input id="password" name="password" type="password" :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" />
    </div>

    <div class="col-12 col-md-6">
        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
    </div>

    <div class="col-12">
        <x-input-label value="Role" />
        <div class="d-flex flex-wrap gap-3 mt-1">
            @foreach ($roles as $role)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                        id="role-{{ $role->id }}" @checked(in_array($role->id, $selectedRoleIds))>
                    <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('role_ids')" />
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active"
                @checked(old('is_active', $user?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Akun aktif</label>
        </div>
    </div>
</div>
