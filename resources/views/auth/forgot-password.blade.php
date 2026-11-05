<x-guest-layout>
    <div class="mb-4">
        <h1 class="h4 fw-semibold mb-1">Lupa password?</h1>
        <p class="text-secondary small mb-0">
            {{ __('Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-100 py-2">
            {{ __('Kirim Tautan Reset Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
