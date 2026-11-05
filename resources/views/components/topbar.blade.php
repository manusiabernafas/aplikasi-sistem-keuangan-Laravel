<header class="app-topbar sticky-top d-flex align-items-center justify-content-between px-3" style="height: 64px;">
    <button type="button" class="btn btn-light d-lg-none" data-sidebar-toggle aria-label="Toggle sidebar">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="d-none d-lg-block text-secondary small">
        {{ now()->translatedFormat('l, d F Y') }}
    </div>

    <div class="d-flex align-items-center gap-2">
    @can('create', \App\Models\Transaction::class)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#quick-transaction-modal">
            <i class="bi bi-lightning-charge-fill me-1"></i><span class="d-none d-sm-inline">Transaksi Cepat</span>
        </button>
    @endcan

    @php
        $unreadNotifications = auth()->user()->unreadNotifications()->latest()->limit(6)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();
    @endphp
    <div class="dropdown">
        <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-6"></i>
            @if ($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px;">
            <div class="px-3 py-2 border-bottom fw-semibold small">Notifikasi</div>
            @if ($unreadNotifications->isEmpty())
                <div class="px-3 py-4 text-center text-secondary small">Tidak ada notifikasi baru</div>
            @else
                @foreach ($unreadNotifications as $notification)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="border-bottom">
                        @csrf
                        <button type="submit" class="dropdown-item white-space-normal py-2">
                            <div class="fw-medium small">{{ $notification->data['title'] }}</div>
                            <div class="text-secondary small">{{ Str::limit($notification->data['message'], 60) }}</div>
                        </button>
                    </form>
                @endforeach
            @endif
            <a href="{{ route('notifications.index') }}" class="dropdown-item text-center small py-2 text-primary">Lihat semua</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-semibold" style="width:2rem;height:2rem;font-size:.8rem;">
                {{ Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
            </span>
            <span class="d-none d-sm-inline small fw-medium">{{ auth()->user()->name }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                </form>
            </li>
        </ul>
    </div>
    </div>
</header>
