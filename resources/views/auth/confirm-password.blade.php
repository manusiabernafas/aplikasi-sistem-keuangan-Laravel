<x-guest-layout>
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Konfirmasi password</h1>
        <p class="text-secondary small mb-0">
            {{ __('Ini adalah area aman. Silakan konfirmasi password Anda sebelum melanjutkan.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Konfirmasi') }}
        </x-primary-button>
    </form>
</x-guest-layout>
