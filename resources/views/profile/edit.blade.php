<x-app-layout>
    <x-page-header title="Profil Saya" description="Kelola informasi akun dan keamanan Anda." />

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card shadow-sm border-danger-subtle">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
