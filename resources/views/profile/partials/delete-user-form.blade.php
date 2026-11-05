<section>
    <header class="mb-3">
        <h2 class="h6 fw-semibold mb-1 text-danger">{{ __('Hapus Akun') }}</h2>
        <p class="text-secondary small mb-0">
            {{ __('Setelah akun dihapus, seluruh data terkait akan dihapus permanen. Pastikan Anda yakin sebelum melanjutkan.') }}
        </p>
    </header>

    <x-danger-button data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
        {{ __('Hapus Akun') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" max-width="md">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h2 class="h6 fw-semibold mb-0">{{ __('Yakin ingin menghapus akun Anda?') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary small">
                    {{ __('Seluruh data akan dihapus permanen. Masukkan password Anda untuk konfirmasi.') }}
                </p>

                <x-input-label for="password" value="{{ __('Password') }}" />
                <x-text-input id="password" name="password" type="password" placeholder="{{ __('Password') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>
            <div class="modal-footer">
                <x-secondary-button data-bs-dismiss="modal">{{ __('Batal') }}</x-secondary-button>
                <x-danger-button>{{ __('Hapus Akun') }}</x-danger-button>
            </div>
        </form>
    </x-modal>

    @if ($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Modal(document.getElementById('confirm-user-deletion')).show();
            });
        </script>
    @endif
</section>
